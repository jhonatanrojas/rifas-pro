<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RafflePrizeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image_path' => $this->image_path ? Storage::disk('public')->url($this->image_path) : null,
            'sort_order' => (int) $this->sort_order,
            'is_featured' => (bool) $this->is_featured,
        ];
    }
}
