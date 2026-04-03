<?php

namespace App\Actions;

use App\DTOs\AffiliateCodeDTO;
use App\Models\Affiliate;
use Illuminate\Support\Str;

class GenerateAffiliateCodeAction
{
    public function execute(AffiliateCodeDTO $dto): Affiliate
    {
        $existing = Affiliate::where('user_id', $dto->userId)->first();

        if ($existing) {
            return $existing;
        }

        do {
            $code = Str::upper(Str::random(8));
        } while (Affiliate::where('code', $code)->exists());

        return Affiliate::create([
            'user_id'         => $dto->userId,
            'code'            => $code,
            'commission_rate' => config('affiliate.default_commission_rate', 0.05),
            'total_earned'    => 0,
            'total_withdrawn' => 0,
        ]);
    }
}
