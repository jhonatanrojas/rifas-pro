<?php

namespace App\Services\OCR;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TesseractOCRService implements OCRServiceInterface
{
    public function extract(string $imagePath): OCRResult
    {
        $absolutePath = Storage::path($imagePath);

        if (! file_exists($absolutePath)) {
            Log::warning("TesseractOCRService: file not found at {$absolutePath}");
            return new OCRResult(null, null, null, null, 0.0);
        }

        $output = [];
        $returnCode = 0;
        exec("tesseract " . escapeshellarg($absolutePath) . " stdout -l spa 2>/dev/null", $output, $returnCode);

        $rawText = implode("\n", $output);

        return $this->parseText($rawText);
    }

    private function parseText(string $text): OCRResult
    {
        $amount    = $this->extractAmount($text);
        $reference = $this->extractReference($text);
        $date      = $this->extractDate($text);
        $bank      = $this->extractBank($text);

        $fieldsFound   = array_filter([$amount, $reference, $date, $bank]);
        $confidenceScore = count($fieldsFound) / 4;

        return new OCRResult(
            amount:          $amount,
            reference:       $reference,
            date:            $date,
            bank:            $bank,
            confidenceScore: (float) $confidenceScore,
            rawText:         $text,
        );
    }

    private function extractAmount(string $text): ?float
    {
        if (preg_match('/\b(\d{1,3}(?:[.,]\d{3})*(?:[.,]\d{2})?)\b/', $text, $matches)) {
            $normalized = str_replace(['.', ','], ['', '.'], $matches[1]);
            return (float) $normalized;
        }
        return null;
    }

    private function extractReference(string $text): ?string
    {
        if (preg_match('/(?:ref(?:erencia)?|número|nro)[:\s#]*([A-Z0-9\-]{6,20})/i', $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    private function extractDate(string $text): ?string
    {
        if (preg_match('/\b(\d{2}[\/\-]\d{2}[\/\-]\d{4})\b/', $text, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function extractBank(string $text): ?string
    {
        $banks = ['Banesco', 'Mercantil', 'Venezuela', 'Provincial', 'BOD', 'Bicentenario'];
        foreach ($banks as $bank) {
            if (stripos($text, $bank) !== false) {
                return $bank;
            }
        }
        return null;
    }
}
