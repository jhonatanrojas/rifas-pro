<?php

namespace Database\Factories;

use App\Models\Raffle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'raffle_id'      => Raffle::factory(),
            'number'         => fake()->unique()->numberBetween(1, 9999),
            'status'         => 'available',
            'reserved_at'    => null,
            'reserved_until' => null,
            'user_id'        => null,
            'order_id'       => null,
            'version'        => 1,
        ];
    }

    public function sold(): static
    {
        return $this->state(['status' => 'sold']);
    }

    public function reserved(): static
    {
        return $this->state([
            'status'         => 'reserved',
            'reserved_at'    => now(),
            'reserved_until' => now()->addMinutes(15),
        ]);
    }

    public function winner(): static
    {
        return $this->state(['status' => 'winner']);
    }
}
