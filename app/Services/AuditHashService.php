<?php

namespace App\Services;

use App\Models\Ticket;

class AuditHashService
{
    public function generateParticipantsHash(int $raffleId): string
    {
        $tickets = Ticket::where('raffle_id', $raffleId)
            ->where('status', 'sold')
            ->orderBy('number')
            ->get(['number', 'user_id', 'order_id'])
            ->toArray();

        $json = json_encode($tickets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash('sha256', $json);
    }

    public function buildParticipantsSnapshot(int $raffleId): array
    {
        return Ticket::where('raffle_id', $raffleId)
            ->where('status', 'sold')
            ->orderBy('number')
            ->get(['number', 'user_id', 'order_id'])
            ->toArray();
    }
}
