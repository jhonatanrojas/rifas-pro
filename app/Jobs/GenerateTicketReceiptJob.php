<?php

namespace App\Jobs;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        $pdf = Pdf::loadView('receipts.ticket', ['order' => $order]);

        $path = "receipts/" . now()->format('Y/m') . "/order-{$order->id}.pdf";
        Storage::put("public/" . $path, $pdf->output());

        $metadata = $order->metadata ?? [];
        $metadata['receipt_path'] = $path;
        $order->update(['metadata' => $metadata]);

        Log::info("GenerateTicketReceiptJob: receipt generated at {$path} for order #{$order->id}.");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateTicketReceiptJob failed for order #{$this->orderId}: " . $exception->getMessage());
    }
}
