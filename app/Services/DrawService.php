<?php

namespace App\Services;

use App\DTOs\ExecuteDrawDTO;
use App\Exceptions\Domain\DrawAlreadyExecutedException;
use App\Jobs\SendWhatsAppTicketJob;
use App\Models\DrawAudit;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\Winner;
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

            DrawAudit::create([
                'raffle_id'              => $raffle->id,
                'participants_hash'      => $participantsHash,
                'participants_snapshot'  => $participantsSnapshot,
                'algorithm_version'      => '1.0',
                'seed'                   => null,
                'drawn_at'               => now(),
                'created_by'             => $dto->adminUserId,
            ]);

            $soldTicketIds = Ticket::where('raffle_id', $raffle->id)
                ->where('status', 'sold')
                ->pluck('id')
                ->toArray();

            if (empty($soldTicketIds)) {
                $raffle->update(['status' => 'drawn']);
                return collect();
            }

            $winnerIndex    = random_int(0, count($soldTicketIds) - 1);
            $winnerTicketId = $soldTicketIds[$winnerIndex];
            $winnerTicket   = Ticket::with('user')->findOrFail($winnerTicketId);

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
