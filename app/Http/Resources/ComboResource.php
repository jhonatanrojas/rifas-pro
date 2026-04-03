<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComboResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $ticketPrice = (float) ($this->raffle?->ticket_price ?? 0);
        $originalPrice = $ticketPrice * (int) $this->quantity;

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => (float) $this->price,
            'label' => $this->label,
            'is_active' => (bool) $this->is_active,
            'original_price' => $originalPrice,
            'savings_percentage' => $ticketPrice > 0 && $originalPrice > 0
                ? max(0, (int) round((1 - ($this->price / $originalPrice)) * 100))
                : 0,
        ];
    }
}
