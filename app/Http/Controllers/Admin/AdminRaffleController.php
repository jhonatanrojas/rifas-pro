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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'ticket_price' => 'required|numeric|min:0',
            'total_tickets' => 'required|integer|min:1',
            'currency' => 'required|string|size:3',
            'draw_date' => 'nullable|date',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['status'] = 'draft';

        $raffle = Raffle::create($validated);

        // Generate tickets if needed via service
        // (appended to background job later)

        return redirect()->route('admin.raffles.index')->with('success', 'Rifa creada exitosamente');
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
