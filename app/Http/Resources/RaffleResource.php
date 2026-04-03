<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaffleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'cover_image' => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
            'ticket_price' => (float) $this->ticket_price,
            'currency' => $this->currency,
            'total_tickets' => $this->total_tickets,
            'sold_count' => $this->sold_count,
            'status' => $this->status,
            'draw_date' => $this->draw_date,
            'is_featured' => (bool) $this->is_featured,
            'tickets_available_count' => $this->total_tickets - $this->sold_count,
            'progress_percentage' => $this->total_tickets > 0 
                ? round(($this->sold_count / $this->total_tickets) * 100, 2) 
                : 0,
            'combos' => ComboResource::collection($this->whenLoaded('combos')),
        ];
    }
}
