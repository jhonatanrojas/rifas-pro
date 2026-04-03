<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Raffle>
 */
class RaffleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'owner_id'      => User::factory(),
            'title'         => $title,
            'slug'          => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 99999),
            'description'   => fake()->paragraph(),
            'cover_image'   => null,
            'total_tickets' => fake()->randomElement([100, 500, 1000, 5000]),
            'ticket_price'  => fake()->randomFloat(2, 1, 50),
            'currency'      => fake()->randomElement(['USD', 'VES', 'COP']),
            'exchange_rate' => null,
            'draw_type'     => 'internal_random',
            'draw_date'     => now()->addDays(30),
            'status'        => 'active',
            'sold_count'    => 0,
            'goal_amount'   => null,
            'is_featured'   => false,
            'settings'      => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function drawn(): static
    {
        return $this->state(['status' => 'drawn']);
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function paused(): static
    {
        return $this->state(['status' => 'paused']);
    }
}
