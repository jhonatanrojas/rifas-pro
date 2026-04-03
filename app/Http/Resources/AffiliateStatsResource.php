<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AffiliateStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'total_clicks' => $this->clicks_count ?? 0,
            'total_conversions' => $this->conversions_count ?? 0,
            'total_commissions' => (float) ($this->commissions_sum_amount ?? 0),
            'pending_commissions' => (float) ($this->pending_commissions_sum_amount ?? 0),
        ];
    }
}
