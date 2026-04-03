<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RaffleResource;
use App\Models\Raffle;
use App\Services\TicketNumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminRaffleController extends Controller
{
    public function index()
    {
        $raffles = Raffle::with(['combos', 'prizes'])
            ->withCount(['tickets', 'tickets as sold_count' => function ($query) {
                $query->where('status', 'sold');
            }])
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Raffle/Index', [
            'raffles' => RaffleResource::collection($raffles),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Raffle/CreateOrEdit');
    }

    public function store(Request $request, TicketNumberGeneratorService $ticketGenerator)
    {
        $validated = $this->validateRaffle($request);
        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        $validated['owner_id'] = $request->user()->id;

        $combos = $validated['combos'] ?? [];
        $prizes = $validated['prizes'] ?? [];
        unset($validated['combos'], $validated['prizes']);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('raffles', 'public');
        }

        DB::transaction(function () use ($validated, $request, $combos, $prizes, $ticketGenerator) {
            $raffle = Raffle::create($validated);
            $this->syncCombos($raffle, $combos);
            $this->syncPrizes($raffle, $request, $prizes);
            $ticketGenerator->generateForRaffle($raffle);
        });

        return redirect()->route('admin.raffles.index')->with('success', 'Rifa creada exitosamente');
    }

    public function update(Request $request, Raffle $raffle, TicketNumberGeneratorService $ticketGenerator)
    {
        $validated = $this->validateRaffle($request);
        $combos = $validated['combos'] ?? [];
        $prizes = $validated['prizes'] ?? [];
        unset($validated['combos'], $validated['prizes']);

        if ($request->hasFile('cover_image')) {
            if ($raffle->cover_image) {
                Storage::disk('public')->delete($raffle->cover_image);
            }

            $validated['cover_image'] = $request->file('cover_image')->store('raffles', 'public');
        }

        DB::transaction(function () use ($raffle, $validated, $request, $combos, $prizes, $ticketGenerator) {
            $raffle->update($validated);
            $this->syncCombos($raffle, $combos);
            $this->syncPrizes($raffle, $request, $prizes);
            $ticketGenerator->generateForRaffle($raffle);
        });

        return redirect()->route('admin.raffles.index')->with('success', 'Rifa actualizada con éxito');
    }

    public function destroy(Raffle $raffle)
    {
        if ($raffle->tickets()->where('status', 'sold')->exists()) {
            return back()->with('error', 'No se puede eliminar una rifa con boletos vendidos.');
        }

        $raffle->delete();

        return redirect()->route('admin.raffles.index')->with('success', 'Rifa eliminada correctamente.');
    }

    public function edit(Raffle $raffle)
    {
        return Inertia::render('Admin/Raffle/CreateOrEdit', [
            'raffle' => new RaffleResource($raffle->load(['combos', 'prizes'])),
        ]);
    }

    public function show(Raffle $raffle)
    {
        $raffle->load([
            'combos',
            'prizes',
            'tickets' => fn ($q) => $q->where('status', 'sold')->limit(20),
        ]);
        $raffle->loadCount(['tickets as sold_count' => fn ($q) => $q->where('status', 'sold')]);

        $stats = [
            'total_sales' => $raffle->orders()->where('status', 'paid')->sum('total'),
            'progress' => (int) $raffle->total_tickets > 0 ? ($raffle->sold_count / (int) $raffle->total_tickets) * 100 : 0,
            'recent_payments' => $raffle->payments()->with('user')->latest()->limit(10)->get(),
        ];

        return Inertia::render('Admin/Raffle/Show', [
            'raffle' => new RaffleResource($raffle),
            'stats' => $stats,
        ]);
    }

    protected function validateRaffle(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string|max:120',
            'ticket_price' => 'required|numeric|min:0',
            'total_tickets' => 'required|integer|min:1',
            'currency' => 'required|string|size:3',
            'draw_date' => 'nullable|date',
            'draw_type' => 'nullable|in:internal_random,external_lottery',
            'status' => 'nullable|in:draft,active,paused,drawn,cancelled',
            'is_featured' => 'nullable|boolean',
            'cover_image' => 'nullable|image|max:4096',
            'combos' => 'nullable|array',
            'combos.*.quantity' => 'required|integer|min:2',
            'combos.*.price' => 'required|numeric|min:0',
            'combos.*.label' => 'nullable|string|max:255',
            'prizes' => 'nullable|array',
            'prizes.*.title' => 'required|string|max:255',
            'prizes.*.description' => 'nullable|string|max:2000',
            'prizes.*.is_featured' => 'nullable|boolean',
            'prizes.*.image' => 'nullable|image|max:4096',
        ]);

        $validated['draw_type'] = $validated['draw_type'] ?? 'internal_random';
        $validated['status'] = $validated['status'] ?? 'draft';
        $validated['is_featured'] = (bool) ($validated['is_featured'] ?? false);

        return $validated;
    }

    protected function syncCombos(Raffle $raffle, array $combos): void
    {
        $raffle->combos()->delete();

        foreach ($combos as $combo) {
            if (! isset($combo['quantity'], $combo['price'])) {
                continue;
            }

            $raffle->combos()->create([
                'quantity' => $combo['quantity'],
                'price' => $combo['price'],
                'label' => $combo['label'] ?? null,
                'is_active' => true,
            ]);
        }
    }

    protected function syncPrizes(Raffle $raffle, Request $request, array $prizes): void
    {
        $raffle->prizes()->delete();

        foreach ($prizes as $index => $prize) {
            if (empty($prize['title'])) {
                continue;
            }

            $imagePath = null;
            if ($request->hasFile("prizes.$index.image")) {
                $imagePath = $request->file("prizes.$index.image")->store('raffle-prizes', 'public');
            }

            $raffle->prizes()->create([
                'title' => $prize['title'],
                'description' => $prize['description'] ?? null,
                'image_path' => $imagePath,
                'sort_order' => $index,
                'is_featured' => (bool) ($prize['is_featured'] ?? false),
            ]);
        }
    }
}
