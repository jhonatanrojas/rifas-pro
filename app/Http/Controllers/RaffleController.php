<?php

namespace App\Http\Controllers;

use App\Http\Resources\ComboResource;
use App\Http\Resources\TicketResource;
use App\Http\Resources\RaffleResource;
use App\Models\Raffle;
use Inertia\Inertia;

class RaffleController extends Controller
{
    /**
     * Display a listing of active raffles.
     */
    public function index()
    {
        $raffles = Raffle::active()->with(['combos', 'prizes'])->paginate(12);
        
        return Inertia::render('Raffle/Index', [
            'raffles' => RaffleResource::collection($raffles)
        ]);
    }

    /**
     * Display the specified raffle.
     */
    public function show(Raffle $raffle)
    {
        $raffle->load(['combos', 'prizes']);
        $tickets = $raffle->tickets()->with('raffle')->orderBy('number', 'asc')->get();
        
        return Inertia::render('Raffle/Show', [
            'raffle' => (new RaffleResource($raffle))->resolve(request()),
            'tickets' => TicketResource::collection($tickets)->resolve(request()),
            'combos' => ComboResource::collection($raffle->combos)->resolve(request()),
            'tickets_stats' => [
                'available' => $raffle->tickets()->available()->count(),
                'sold' => $raffle->tickets()->sold()->count(),
                'reserved' => $raffle->tickets()->reserved()->count(),
            ]
        ]);
    }
}
