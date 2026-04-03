<?php

namespace Database\Factories;

use App\Models\Raffle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DrawAudit>
 */
class DrawAuditFactory extends Factory
{
    public function definition(): array
    {
        return [
            'raffle_id'             => Raffle::factory(),
            'participants_hash'     => hash('sha256', fake()->uuid()),
            'participants_snapshot' => [],
            'algorithm_version'     => '1.0',
            'seed'                  => null,
            'drawn_at'              => now(),
            'created_by'            => User::factory(),
        ];
    }
}
