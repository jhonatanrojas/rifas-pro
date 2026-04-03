<?php

namespace App\DTOs;

readonly class ApplyCouponDTO
{
    public function __construct(
        public string $couponCode,
        public int $orderId,
        public int $raffleId,
    ) {}
}
