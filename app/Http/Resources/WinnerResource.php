<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WinnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_name' => $this->user->name,
            'raffle_title' => $this->raffle->title,
            'ticket_number' => $this->ticket->number,
            'prize' => $this->prize_description,
            'won_at' => $this->created_at,
        ];
    }
}
