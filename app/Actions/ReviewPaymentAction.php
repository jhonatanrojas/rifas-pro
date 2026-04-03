<?php

namespace App\Actions;

use App\Events\RaffleMetricsUpdated;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Raffle;
use App\Services\Notifications\WhatsAppServiceInterface;
use Illuminate\Support\Facades\DB;

class ReviewPaymentAction
{
    public function __construct(protected WhatsAppServiceInterface $whatsapp)
    {
    }

    public function execute(Payment $payment, string $status, ?string $notes = null): void
    {
        DB::transaction(function () use ($payment, $status, $notes) {
            $payment->update([
                'status' => $status, // approved | rejected
                'notes'  => $notes,
            ]);

            $order = $payment->order;

            if ($status === 'approved') {
                $order->update(['status' => 'paid']);
                
                // Finalize ticket confirmation (already reserved, just confirm sale)
                $order->tickets()->update(['status' => 'sold']);
                
                // Update raffle sold count
                $raffle = $order->raffle;
                $raffle->increment('sold_count', $order->ticket_count);

                // Notify User via WhatsApp (Phase 6.3)
                $this->whatsapp->sendTicket($order->user, $order, $payment->receipt_image_path ?? '');
            } else {
                $order->update(['status' => 'payment_rejected']);
                // Don't release tickets here immediately? Or release them?
                // Phase 2 logic: Release after 15m expiration if not paid.
            }

            // Trigger Real-time metrics
            $today = now()->startOfDay();
            $metrics = [
                'daily' => [
                    'sales_usd' => Order::where('status', 'paid')->where('currency', 'USD')->where('created_at', '>=', $today)->sum('total'),
                    'sales_ves' => Order::where('status', 'paid')->where('currency', 'VES')->where('created_at', '>=', $today)->sum('total'),
                    'tickets_sold_today' => Order::where('status', 'paid')->where('created_at', '>=', $today)->sum('ticket_count'),
                    'pending_reviews' => Payment::where('status', 'pending')->count(),
                ],
                'orders' => Order::with(['user', 'raffle'])->latest()->limit(10)->get(),
            ];

            broadcast(new RaffleMetricsUpdated($metrics))->toOthers();
        });
    }
}
