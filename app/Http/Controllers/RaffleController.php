<?php

namespace App\Http\Controllers;

use App\Models\Raffle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RaffleController extends Controller
{
    /**
     * Mostrar la página de detalles de la rifa.
     */
    public function show($slug)
    {
        $raffle = Raffle::where('slug', $slug)
            ->with(['combos' => function ($query) {
                $query->where('is_active', true)->orderBy('quantity', 'asc');
            }])
            ->firstOrFail();

        // Para evitar mandar miles de tickets enteros, mandamos solo un map de estado: [ number => status ]
        // o envíamos su id y estado.
        // Lo óptimo es mandar solo el ID, número y estado para construir la grilla.
        // Si hay miles, solo mandaremos los status agrupados, pero por el momento traer el array es manejable
        $tickets = $raffle->tickets()
            ->select('id', 'number', 'status')
            ->orderBy('number', 'asc')
            ->get();

        return Inertia::render('Raffle/Show', [
            'raffle' => $raffle,
            'tickets' => $tickets,
            'combos' => $raffle->combos,
        ]);
    }
}
