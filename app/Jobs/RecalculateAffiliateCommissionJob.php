<?php

namespace App\Jobs;

use App\Models\ReferralConversion;
use App\Services\AffiliateTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecalculateAffiliateCommissionJob implements ShouldQueue, ShouldBeUnique
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

    public function handle(AffiliateTrackingService $service): void
    {
        $conversion = ReferralConversion::with('affiliate')
            ->where('order_id', $this->orderId)
            ->where('status', 'pending')
            ->first();

        if (! $conversion) {
            Log::info("RecalculateAffiliateCommissionJob: no pending conversion for order #{$this->orderId}.");
            return;
        }

        $order             = $conversion->order;
        $newCommission     = round(
            (float) $order->total * (float) $conversion->affiliate->commission_rate,
            4
        );

        $conversion->update(['commission_amount' => $newCommission]);

        $service->creditCommission($conversion);

        Log::info("RecalculateAffiliateCommissionJob: credited {$newCommission} for order #{$this->orderId}.");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("RecalculateAffiliateCommissionJob failed for order #{$this->orderId}: " . $exception->getMessage());
    }
}
