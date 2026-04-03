<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Affiliate>
 */
class AffiliateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'code'             => Str::upper(Str::random(8)),
            'commission_rate'  => fake()->randomFloat(4, 0.03, 0.10),
            'total_earned'     => 0,
            'total_withdrawn'  => 0,
        ];
    }
}
