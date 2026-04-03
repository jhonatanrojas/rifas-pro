<?php

namespace App\Services\OCR;

interface OCRServiceInterface
{
    public function extract(string $imagePath): OCRResult;
}
