<?php

namespace App\Actions;

use App\Events\RaffleMetricsUpdated;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Raffle;
use App\Notifications\PaymentReviewedNotification;
use App\Services\Notifications\WhatsAppServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReviewPaymentAction
{
    public function __construct(protected WhatsAppServiceInterface $whatsapp)
    {
    }

    public function execute(Payment $payment, string $status, ?string $notes = null, ?int $reviewerId = null): void
    {
        DB::transaction(function () use ($payment, $status, $notes, $reviewerId) {
            $payment->loadMissing(['order.user', 'order.raffle', 'order.tickets']);

            $payment->update([
                'status' => $status, // approved | rejected
                'notes'  => $notes,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ]);

            $order = $payment->order;

            if ($status === 'approved') {
                $order->update(['status' => 'paid']);
                
                // Finalize ticket confirmation (already reserved, just confirm sale)
                $order->tickets()->update(['status' => 'sold']);
                
                // Update raffle sold count
                $raffle = $order->raffle;
                $raffle->increment('sold_count', $order->tickets()->count());

                // Notify User via WhatsApp (Phase 6.3)
                $receiptUrl = $payment->receipt_image_path
                    ? Storage::disk('public')->url($payment->receipt_image_path)
                    : '';

                $this->whatsapp->sendTicket($order->user, $order, $receiptUrl);
            } else {
                $order->update(['status' => 'payment_rejected']);
                // Don't release tickets here immediately? Or release them?
                // Phase 2 logic: Release after 15m expiration if not paid.
            }

            $order->user?->notify(new PaymentReviewedNotification($payment, $status));

            // Trigger Real-time metrics
            $today = now()->startOfDay();
            $metrics = [
                'daily' => [
                    'sales_usd' => Order::where('status', 'paid')->where('currency', 'USD')->where('created_at', '>=', $today)->sum('total'),
                    'sales_ves' => Order::where('status', 'paid')->where('currency', 'VES')->where('created_at', '>=', $today)->sum('total'),
                    'tickets_sold_today' => DB::table('order_tickets')
                        ->join('orders', 'orders.id', '=', 'order_tickets.order_id')
                        ->where('orders.status', 'paid')
                        ->where('orders.created_at', '>=', $today)
                        ->count(),
                    'pending_reviews' => Payment::where('status', 'pending')->count(),
                ],
                'orders' => Order::with(['user', 'raffle'])->latest()->limit(10)->get(),
            ];

            broadcast(new RaffleMetricsUpdated($metrics))->toOthers();
        });
    }
}
