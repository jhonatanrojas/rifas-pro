<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'        => Str::upper(Str::random(8)),
            'type'        => fake()->randomElement(['percent', 'fixed']),
            'value'       => fake()->randomFloat(2, 5, 30),
            'max_uses'    => fake()->numberBetween(10, 100),
            'used_count'  => 0,
            'valid_from'  => now()->subDay(),
            'valid_until' => now()->addDays(30),
            'raffle_id'   => null,
        ];
    }

    public function expired(): static
    {
        return $this->state([
            'valid_from'  => now()->subDays(10),
            'valid_until' => now()->subDay(),
        ]);
    }

    public function exhausted(): static
    {
        return $this->state(fn (array $attrs) => [
            'max_uses'   => 5,
            'used_count' => 5,
        ]);
    }
}
