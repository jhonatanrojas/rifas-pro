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
            'category' => $this->category,
            'cover_image' => $this->cover_image ? asset('storage/' . $this->cover_image) : null,
            'ticket_price' => (float) $this->ticket_price,
            'currency' => $this->currency,
            'total_tickets' => (int) $this->total_tickets,
            'sold_count' => (int) $this->sold_count,
            'status' => $this->status,
            'draw_date' => $this->draw_date,
            'is_featured' => (bool) $this->is_featured,
            'number_digits' => $this->number_digits,
            'number_range_label' => $this->number_range_label,
            'tickets_available_count' => max(0, (int) $this->total_tickets - (int) $this->sold_count),
            'progress_percentage' => (int) $this->total_tickets > 0 
                ? round(((int) $this->sold_count / (int) $this->total_tickets) * 100, 2) 
                : 0,
            'combos' => $this->whenLoaded('combos', function () {
                $this->combos->each->setRelation('raffle', $this->resource);

                return ComboResource::collection($this->combos);
            }),
            'prizes' => RafflePrizeResource::collection($this->whenLoaded('prizes')),
        ];
    }
}
