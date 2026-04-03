<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\TicketReceiptGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateTicketReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 60, 120];

    public function __construct(
        public readonly int $orderId,
    ) {}

    public function handle(): void
    {
        $order = Order::with(['user', 'raffle', 'tickets', 'payment'])->find($this->orderId);

        if (! $order) {
            Log::warning("GenerateTicketReceiptJob: order #{$this->orderId} not found.");
            return;
        }

        $receipt = app(TicketReceiptGenerator::class)->generate($order);

        $metadata = $order->metadata ?? [];
        $metadata['receipt_path'] = $receipt['path'];
        $metadata['receipt_url'] = $receipt['url'];
        $metadata['receipt_verification_url'] = $receipt['verification_url'];
        $metadata['receipt_hash'] = $receipt['hash'];
        $order->update(['metadata' => $metadata]);

        Log::info("GenerateTicketReceiptJob: receipt generated at {$receipt['path']} for order #{$order->id}.");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateTicketReceiptJob failed for order #{$this->orderId}: " . $exception->getMessage());
    }
}
