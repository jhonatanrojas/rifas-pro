<?php

namespace App\DTOs;

readonly class ExecuteDrawDTO
{
    public function __construct(
        public int $raffleId,
        public int $adminUserId,
        public string $prizeDescription,
        public string $executionMode = 'automatic',
        public ?int $winningNumber = null,
        public ?string $externalReference = null,
    ) {}
}
