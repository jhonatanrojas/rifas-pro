<?php

namespace App\Services;

use App\Models\Raffle;
use Illuminate\Support\Facades\DB;

class TicketNumberGeneratorService
{
    public function generateForRaffle(Raffle $raffle): void
    {
        $chunkSize = 500;
        $total = $raffle->total_tickets;
        $raffleId = $raffle->id;
        $now = now()->toDateTimeString();

        for ($start = 1; $start <= $total; $start += $chunkSize) {
            $end = min($start + $chunkSize - 1, $total);

            $rows = [];
            for ($number = $start; $number <= $end; $number++) {
                $rows[] = [
                    'raffle_id'  => $raffleId,
                    'number'     => $number,
                    'status'     => 'available',
                    'version'    => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('tickets')->insertOrIgnore($rows);
        }
    }
}
