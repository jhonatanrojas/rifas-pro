<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Raffle;
use App\Http\Resources\RaffleResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class AdminRaffleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $raffles = Raffle::withCount(['tickets', 'tickets as sold_count' => function ($query) {
            $query->where('status', 'sold');
        }])->latest()->paginate(10);
        
        return Inertia::render('Admin/Raffle/Index', [
            'raffles' => RaffleResource::collection($raffles)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Raffle/CreateOrEdit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'ticket_price' => 'required|numeric|min:0',
            'total_tickets' => 'required|integer|min:1',
            'currency' => 'required|string|size:3',
            'draw_date' => 'nullable|date',
            'cover_image' => 'nullable|image|max:2048',
            'combos' => 'nullable|array',
            'combos.*.quantity' => 'required|integer|min:2',
            'combos.*.price' => 'required|numeric|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(5);
        $validated['status'] = 'draft';
        $validated['owner_id'] = $request->user()->id;

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('raffles', 'public');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $request) {
            $raffle = Raffle::create($validated);
            
            if ($request->combos) {
                foreach ($request->combos as $combo) {
                    $raffle->combos()->create($combo);
                }
            }

            // Optional: Dispatch ticket generation job here
        });

        return redirect()->route('admin.raffles.index')->with('success', 'Rifa creada exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Raffle $raffle)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'ticket_price' => 'required|numeric|min:0',
            'total_tickets' => 'required|integer|min:1',
            'currency' => 'required|string|size:3',
            'draw_date' => 'nullable|date',
            'cover_image' => 'nullable|image|max:2048',
            'combos' => 'nullable|array',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($raffle->cover_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($raffle->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('raffles', 'public');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($raffle, $validated, $request) {
            $raffle->update($validated);
            
            if ($request->has('combos')) {
                $raffle->combos()->delete();
                foreach ($request->combos as $combo) {
                    $raffle->combos()->create($combo);
                }
            }
        });

        return redirect()->route('admin.raffles.index')->with('success', 'Rifa actualizada con éxito');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Raffle $raffle)
    {
        if ($raffle->tickets()->where('status', 'sold')->exists()) {
            return back()->with('error', 'No se puede eliminar una rifa con boletos vendidos.');
        }

        $raffle->delete();
        return redirect()->route('admin.raffles.index')->with('success', 'Rifa eliminada correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Raffle $raffle)
    {
        return Inertia::render('Admin/Raffle/CreateOrEdit', [
            'raffle' => new RaffleResource($raffle->load('combos'))
        ]);
    }

    public function show(Raffle $raffle)
    {
        $raffle->load(['combos', 'tickets' => fn($q) => $q->where('status', 'sold')->limit(20)]);
        $raffle->loadCount(['tickets as sold_count' => fn($q) => $q->where('status', 'sold')]);
        
        $stats = [
            'total_sales' => $raffle->orders()->where('status', 'paid')->sum('total'),
            'progress' => $raffle->total_tickets > 0 ? ($raffle->sold_count / $raffle->total_tickets) * 100 : 0,
            'recent_payments' => $raffle->payments()->with('user')->latest()->limit(10)->get(),
        ];

        return Inertia::render('Admin/Raffle/Show', [
            'raffle' => new RaffleResource($raffle),
            'stats'  => $stats
        ]);
    }
}
