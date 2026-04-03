<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ExpireTicketReservationJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $orderId,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->orderId;
    }

    public function handle(): void
    {
        $order = Order::with(['tickets', 'user'])->find($this->orderId);

        if (! $order || $order->status !== 'pending') {
            return;
        }

        DB::transaction(function () use ($order): void {
            $ticketIds = $order->tickets()->pluck('tickets.id')->toArray();

            Ticket::whereIn('id', $ticketIds)->update([
                'status'         => 'available',
                'user_id'        => null,
                'order_id'       => null,
                'reserved_at'    => null,
                'reserved_until' => null,
            ]);

            $order->update(['status' => 'cancelled']);
        });

        // Notify user by email if available
        if ($order->user && $order->user->email) {
            // Mail::to($order->user->email)->send(new ReservationExpiredMail($order));
            // Mail implementation deferred until Fase 6 (notifications)
        }
    }
}
