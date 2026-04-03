<?php

namespace App\Http\Controllers;

use App\DTOs\ExecuteDrawDTO;
use App\Exceptions\Domain\DrawAlreadyExecutedException;
use App\Http\Requests\ExecuteDrawRequest;
use App\Http\Resources\RaffleResource;
use App\Http\Resources\WinnerWallResource;
use App\Notifications\WinnerNotification;
use App\Models\Raffle;
use App\Services\DrawService;
use App\Services\Notifications\WhatsAppServiceInterface;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DrawController extends Controller
{
    public function __construct(
        protected WhatsAppServiceInterface $whatsapp,
        protected DrawService $drawService,
    ) {
    }

    public function show(Raffle $raffle)
    {
        $raffle->load('drawAudit');
        $winners = $raffle->winners()->with(['user', 'ticket', 'raffle'])->latest()->get();

        return Inertia::render('Draw/Show', [
            'raffle' => (new RaffleResource($raffle))->resolve(request()),
            'winner' => $winners->first()
                ? (new WinnerWallResource($winners->first()))->resolve(request())
                : null,
            'winners' => WinnerWallResource::collection($winners)->resolve(request()),
            'auditHash' => $raffle->drawAudit?->participants_hash,
            'drawAudit' => $raffle->drawAudit,
            'canExecute' => Gate::allows('execute', $raffle) && ! $raffle->drawAudit,
        ]);
    }

    public function execute(ExecuteDrawRequest $request, Raffle $raffle)
    {
        Gate::authorize('execute', $raffle);

        try {
            $winners = $this->drawService->execute(new ExecuteDrawDTO(
                raffleId: $raffle->id,
                adminUserId: $request->user()->id,
                prizeDescription: $request->prize_description,
                executionMode: $request->input('execution_mode', $raffle->draw_type === 'external_lottery' ? 'manual_external' : 'automatic'),
                winningNumber: $request->filled('winning_number') ? (int) $request->input('winning_number') : null,
                externalReference: $request->input('external_reference'),
            ));

            if ($winners->isEmpty()) {
                return back()->withErrors(['error' => 'No hay boletos vendidos para sortear.']);
            }

            $winner = $winners->first();

            if ($winner?->user) {
                $winner->user->notify(new WinnerNotification($raffle));
                $this->whatsapp->sendWinnerNotification($winner->user, $raffle);
            }

            $message = $winners->isNotEmpty()
                ? '¡Sorteo ejecutado con éxito!'
                : 'Sorteo ejecutado sin boletos vendidos.';

            return redirect()->route('admin.raffles.show', $raffle)->with('success', $message);
        } catch (DrawAlreadyExecutedException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Error al ejecutar el sorteo: ' . $e->getMessage()]);
        }
    }
}
