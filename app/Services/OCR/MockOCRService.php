<?php

namespace App\Services\OCR;

class MockOCRService implements OCRServiceInterface
{
    public function extract(string $imagePath): OCRResult
    {
        return new OCRResult(
            amount:          100.00,
            reference:       'REF-MOCK-001',
            date:            now()->format('d/m/Y'),
            bank:            'MockBank',
            confidenceScore: 0.99,
            rawText:         'MOCK OCR OUTPUT',
        );
    }
}
