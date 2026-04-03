<?php

namespace App\Services;

use App\DTOs\ReserveTicketsDTO;
use App\Models\Order;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Exceptions\Domain\InsufficientTicketsException;

class TicketReservationService
{
    public function reserve(ReserveTicketsDTO $dto): Order
    {
        $raffle = Raffle::with('combos')->findOrFail($dto->raffleId);
        
        $tickets = collect();
        if ($dto->manualTickets && count($dto->manualTickets) > 0) {
            $tickets = $raffle->tickets()
                ->whereIn('number', $dto->manualTickets)
                ->where('status', 'available')
                ->lockForUpdate()
                ->get();

            if ($tickets->count() !== count($dto->manualTickets)) {
                throw new InsufficientTicketsException('Algunos tickets seleccionados ya no están disponibles.');
            }
        } else {
            $tickets = $raffle->tickets()
                ->where('status', 'available')
                ->inRandomOrder()
                ->limit($dto->quantity)
                ->lockForUpdate()
                ->get();

            if ($tickets->count() < $dto->quantity) {
                 throw new InsufficientTicketsException();
            }
        }

        // 3. Security Check: Calculate real total
        $rafflePrice = $raffle->ticket_price;
        $subtotal = $dto->quantity * $rafflePrice;
        $discount = 0;
        
        $combos = $raffle->combos()->orderByDesc('quantity')->get();
        foreach ($combos as $combo) {
            if ($dto->quantity >= $combo->quantity) {
                $comboSubtotal = $combo->quantity * $rafflePrice;
                $discountPerCombo = $comboSubtotal - $combo->price;
                $comboMultiplier = floor($dto->quantity / $combo->quantity);
                $discountC = $discountPerCombo * $comboMultiplier;
                if ($discountC > $discount) {
                    $discount = $discountC;
                }
            }
        }
        $calculatedTotal = $subtotal - $discount;

        // 4. Create Order
        $order = Order::create([
            'user_id' => $dto->userId,
            'raffle_id' => $raffle->id,
            'status' => 'pending',
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $calculatedTotal,
            'currency' => $raffle->currency,
        ]);

        // 5. Update and attach Tickets
        $ticketIds = $tickets->pluck('id')->toArray();
        $order->tickets()->attach($ticketIds);
        
        Ticket::whereIn('id', $ticketIds)->update([
            'status' => 'reserved',
            'user_id' => $dto->userId,
            'order_id' => $order->id,
            'reserved_at' => now(),
            'reserved_until' => now()->addHours(12) // User has 12 hours window before cancelation
        ]);

        return $order;
    }
}
