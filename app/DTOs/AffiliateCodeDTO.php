<?php

namespace App\DTOs;

readonly class AffiliateCodeDTO
{
    public function __construct(
        public int $userId,
    ) {}
}
