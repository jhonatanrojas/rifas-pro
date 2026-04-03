<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'method' => $this->method,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'reference_number' => $this->reference_number,
            'receipt_image_path' => $this->receipt_image_path ? Storage::disk('public')->url($this->receipt_image_path) : null,
            'status' => $this->status,
            'reviewed_at' => $this->reviewed_at,
        ];
    }
}
