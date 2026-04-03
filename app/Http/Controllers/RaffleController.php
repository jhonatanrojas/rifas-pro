<?php

namespace App\Http\Controllers;

use App\Http\Resources\RaffleResource;
use App\Models\Raffle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RaffleController extends Controller
{
    /**
     * Display a listing of active raffles.
     */
    public function index()
    {
        $raffles = Raffle::active()->with('combos')->paginate(12);
        
        return Inertia::render('Raffle/Index', [
            'raffles' => RaffleResource::collection($raffles)
        ]);
    }

    /**
     * Display the specified raffle.
     */
    public function show(Raffle $raffle)
    {
        $raffle->load('combos');
        
        return Inertia::render('Raffle/Show', [
            'raffle' => new RaffleResource($raffle),
            'tickets_stats' => [
                'available' => $raffle->tickets()->available()->count(),
                'sold' => $raffle->tickets()->sold()->count(),
                'reserved' => $raffle->tickets()->reserved()->count(),
            ]
        ]);
    }
}
