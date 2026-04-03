<?php

namespace App\Services;

use App\DTOs\ExecuteDrawDTO;
use App\Exceptions\Domain\DrawAlreadyExecutedException;
use App\Jobs\SendWhatsAppTicketJob;
use App\Models\DrawAudit;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\Winner;
use App\Exceptions\Domain\InsufficientTicketsException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DrawService
{
    public function __construct(
        private readonly AuditHashService $auditHashService,
    ) {}

    /**
     * @throws DrawAlreadyExecutedException
     */
    public function execute(ExecuteDrawDTO $dto): Collection
    {
        $raffle = Raffle::findOrFail($dto->raffleId);

        if ($raffle->drawAudit()->exists()) {
            throw new DrawAlreadyExecutedException();
        }

        return DB::transaction(function () use ($raffle, $dto): Collection {
            $participantsHash     = $this->auditHashService->generateParticipantsHash($raffle->id);
            $participantsSnapshot = $this->auditHashService->buildParticipantsSnapshot($raffle->id);

            $soldTicketsQuery = Ticket::where('raffle_id', $raffle->id)->where('status', 'sold');
            $soldTickets = $soldTicketsQuery->with('user')->get();

            if ($soldTickets->isEmpty()) {
                DrawAudit::create([
                    'raffle_id'              => $raffle->id,
                    'participants_hash'      => $participantsHash,
                    'participants_snapshot'  => $participantsSnapshot,
                    'algorithm_version'      => '1.1',
                    'seed'                   => null,
                    'execution_mode'         => $dto->executionMode,
                    'external_reference'     => $dto->externalReference,
                    'winning_number'         => $dto->winningNumber,
                    'drawn_at'               => now(),
                    'created_by'             => $dto->adminUserId,
                ]);

                $raffle->update(['status' => 'drawn']);
                return collect();
            }

            DrawAudit::create([
                'raffle_id'              => $raffle->id,
                'participants_hash'      => $participantsHash,
                'participants_snapshot'  => $participantsSnapshot,
                'algorithm_version'      => '1.1',
                'seed'                   => null,
                'execution_mode'         => $dto->executionMode,
                'external_reference'     => $dto->externalReference,
                'winning_number'         => $dto->winningNumber,
                'drawn_at'               => now(),
                'created_by'             => $dto->adminUserId,
            ]);

            $winnerTicket = null;
            if ($dto->executionMode === 'manual_external' && $dto->winningNumber !== null) {
                $winnerTicket = $soldTickets->firstWhere('number', $dto->winningNumber);

                if (! $winnerTicket) {
                    throw new InsufficientTicketsException('El numero ganador externo no corresponde a un ticket vendido.');
                }
            } else {
                $winnerTicket = $soldTickets[random_int(0, $soldTickets->count() - 1)];
            }

            $winner = Winner::create([
                'raffle_id'         => $raffle->id,
                'ticket_id'         => $winnerTicket->id,
                'user_id'           => $winnerTicket->user_id,
                'prize_description' => $dto->prizeDescription,
            ]);

            $winnerTicket->update(['status' => 'winner']);
            $raffle->update(['status' => 'drawn']);

            SendWhatsAppTicketJob::dispatch($winner->id);

            return collect([$winner->load(['ticket', 'user'])]);
        });
    }
}
