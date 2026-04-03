<?php

namespace App\Services\OCR;

readonly class OCRResult
{
    public function __construct(
        public ?float  $amount,
        public ?string $reference,
        public ?string $date,
        public ?string $bank,
        public float   $confidenceScore,
        public string  $rawText = '',
    ) {}

    public function toArray(): array
    {
        return [
            'amount'           => $this->amount,
            'reference'        => $this->reference,
            'date'             => $this->date,
            'bank'             => $this->bank,
            'confidence_score' => $this->confidenceScore,
            'raw_text'         => $this->rawText,
        ];
    }
}
