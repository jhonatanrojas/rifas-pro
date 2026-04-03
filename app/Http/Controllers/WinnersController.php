<?php

namespace App\Http\Controllers;

use App\Http\Resources\WinnerWallResource;
use App\Models\Winner;
use App\Models\Raffle;
use Inertia\Inertia;
use Inertia\Response;

class WinnersController extends Controller
{
    public function index(): Response
    {
        $winners = Winner::with(['raffle', 'ticket', 'user'])
            ->whereHas('raffle', fn ($q) => $q->where('status', 'drawn'))
            ->latest()
            ->get();

        $raffles = Raffle::where('status', 'drawn')
            ->select('id', 'title', 'slug', 'cover_image')
            ->latest()
            ->get();

        return Inertia::render('Winners/Index', [
            'winners' => WinnerWallResource::collection($winners)->resolve(request()),
            'raffles' => $raffles,
        ]);
    }

    public function storeTestimony(\Illuminate\Http\Request $request, Winner $winner)
    {
        $user = $request->user();

        abort_unless($user && ($user->id === $winner->user_id || $user->isAdmin()), 403);

        $validated = $request->validate([
            'testimony' => ['required', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $winner->testimony = $validated['testimony'];

        if ($request->hasFile('photo')) {
            $winner->photo_path = $request->file('photo')->store('winners/testimonies', 'public');
        }

        $winner->save();

        return back()->with('success', 'Tu testimonio fue guardado con éxito.');
    }
}
