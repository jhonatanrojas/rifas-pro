<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExecuteDrawRequest;
use App\Models\Raffle;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Gate;

class DrawController extends Controller
{
    public function show(Raffle $raffle)
    {
        return Inertia::render('Draw/Show', [
            'raffle' => $raffle,
            'winners' => $raffle->winners()->with('user')->get(),
        ]);
    }

    public function execute(ExecuteDrawRequest $request, Raffle $raffle)
    {
        Gate::authorize('execute', $raffle);

        DB::beginTransaction();
        try {
            // Logic for random selection
            $winningTicket = Ticket::where('raffle_id', $raffle->id)
                ->where('status', 'sold') // Only sold tickets can win
                ->inRandomOrder()
                ->first();

            if (!$winningTicket) {
                return redirect()->back()->withErrors(['error' => 'No hay tickets vendidos para realizar el sorteo.']);
            }

            $raffle->winners()->create([
                'user_id' => $winningTicket->user_id,
                'ticket_id' => $winningTicket->id,
                'prize_description' => $request->prize_description,
            ]);

            $raffle->update(['status' => 'completed', 'draw_date' => now()]);

            DB::commit();
            return redirect()->route('raffles.show', $raffle)->with('success', '¡Sorteo ejecutado con éxito!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Error al ejecutar el sorteo: ' . $e->getMessage()]);
        }
    }
}
