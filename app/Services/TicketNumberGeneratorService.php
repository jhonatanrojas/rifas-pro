<?php

namespace App\Services;

use App\Models\Raffle;
use App\Models\Ticket;

class TicketNumberGeneratorService
{
    public function generateForRaffle(Raffle $raffle): int
    {
        $totalTickets = max(0, (int) $raffle->total_tickets);

        if ($totalTickets === 0) {
            return 0;
        }

        $existingNumbers = Ticket::where('raffle_id', $raffle->id)->pluck('number')->all();
        $existingLookup = array_flip($existingNumbers);
        $rows = [];

        for ($number = 0; $number < $totalTickets; $number++) {
            if (isset($existingLookup[$number])) {
                continue;
            }

            $rows[] = [
                'raffle_id' => $raffle->id,
                'number' => $number,
                'status' => 'available',
                'version' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Ticket::insertOrIgnore($chunk);
        }

        return count($rows);
    }
}
