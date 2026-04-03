<?php

namespace App\Http\Controllers;

use App\Models\Raffle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WaitlistController extends Controller
{
    public function join(Request $request, Raffle $raffle): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Debes estar autenticado para unirte a la lista de espera.'], 401);
        }

        // Logic here to attach user to waitlist relationship (needs waitlist table/pivot)
        // For now, let's assume a many-to-many relationship raffle_user_waitlist
        
        //$raffle->waitlistUsers()->syncWithoutDetaching([$user->id]);

        return response()->json(['message' => 'Te has unido a la lista de espera correctamente.']);
    }

    public function leave(Request $request, Raffle $raffle): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // $raffle->waitlistUsers()->detach($user->id);

        return response()->json(['message' => 'Has salido de la lista de espera.']);
    }
}
