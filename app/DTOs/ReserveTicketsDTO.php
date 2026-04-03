<?php

namespace App\DTOs;

readonly class ReserveTicketsDTO
{
    public function __construct(
        public int $raffleId,
        public int $userId,
        public int $quantity,
        public ?array $manualTickets = null,
        public ?string $couponCode = null
    ) {}
}
