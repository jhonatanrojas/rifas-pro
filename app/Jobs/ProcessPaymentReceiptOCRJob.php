<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\OCR\OCRServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentReceiptOCRJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [15, 60, 180];

    public function __construct(
        public readonly int    $paymentId,
        public readonly string $imagePath,
    ) {}

    public function handle(OCRServiceInterface $ocr): void
    {
        $payment = Payment::find($this->paymentId);

        if (! $payment) {
            Log::warning("ProcessPaymentReceiptOCRJob: payment #{$this->paymentId} not found.");
            return;
        }

        $result = $ocr->extract($this->imagePath);

        $payment->update(['ocr_raw_data' => $result->toArray()]);

        if ($result->confidenceScore > 0.85) {
            $updates = [];

            if ($result->reference && ! $payment->reference_number) {
                $updates['reference_number'] = $result->reference;
            }

            if ($result->amount && ! $payment->amount) {
                $updates['amount'] = $result->amount;
            }

            if (! empty($updates)) {
                $payment->update($updates);
            }

            Log::info("ProcessPaymentReceiptOCRJob: high-confidence OCR for payment #{$this->paymentId}.", $result->toArray());
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessPaymentReceiptOCRJob failed for payment #{$this->paymentId}: " . $exception->getMessage());
    }
}
