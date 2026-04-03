<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

readonly class ConfirmPaymentDTO
{
    public function __construct(
        public int $orderId,
        public string $method,
        public float $amount,
        public string $currency,
        public string $referenceNumber,
        public ?UploadedFile $receiptImage = null
    ) {}
}
