<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WinnerWallResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name ?? 'Ganador',
                'avatar' => $this->user?->avatar,
            ],
            'ticket' => [
                'id' => $this->ticket?->id,
                'number' => $this->ticket?->number,
                'display_number' => $this->ticket?->display_number ?? str_pad((string) ($this->ticket?->number ?? 0), $this->raffle?->number_digits ?? 4, '0', STR_PAD_LEFT),
            ],
            'raffle' => [
                'id' => $this->raffle?->id,
                'title' => $this->raffle?->title,
                'slug' => $this->raffle?->slug,
                'cover_image' => $this->raffle?->cover_image,
            ],
            'prize_description' => $this->prize_description,
            'testimony' => $this->testimony,
            'photo_path' => $this->photo_path,
            'drawn_at' => $this->created_at?->toDateTimeString(),
            'notified_at' => $this->notified_at?->toDateTimeString(),
            'claimed_at' => $this->claimed_at?->toDateTimeString(),
            'is_own_winner' => $request->user()?->id === $this->user_id,
        ];
    }
}
