<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $digits = $this->raffle?->number_digits ?? 4;

        return [
            'id' => $this->id,
            'number' => $this->number,
            'display_number' => str_pad((string) $this->number, $digits, '0', STR_PAD_LEFT),
            'status' => $this->status,
        ];
    }
}
