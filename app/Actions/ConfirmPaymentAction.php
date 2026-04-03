<?php

namespace App\Actions;

use App\DTOs\ConfirmPaymentDTO;
use App\Jobs\GenerateTicketReceiptJob;
use App\Jobs\ProcessPaymentReceiptOCRJob;
use App\Models\Order;

class ConfirmPaymentAction
{
    public function execute(ConfirmPaymentDTO $dto): void
    {
        $order = Order::findOrFail($dto->orderId);

        $path = null;
        if ($dto->receiptImage) {
            $path = $dto->receiptImage->store('receipts', 'public');
        }

        $payment = $order->payment()->create([
            'method'             => strtolower($dto->method),
            'amount'             => $dto->amount,
            'currency'           => $dto->currency,
            'reference_number'   => $dto->referenceNumber,
            'receipt_image_path' => $path,
            'status'             => 'pending',
        ]);

        GenerateTicketReceiptJob::dispatch($order->id);

        if ($path) {
            ProcessPaymentReceiptOCRJob::dispatch($payment->id, $path);
        }
    }
}
