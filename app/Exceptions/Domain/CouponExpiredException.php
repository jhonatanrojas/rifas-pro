<?php

namespace App\Exceptions\Domain;

use Exception;

class CouponExpiredException extends Exception
{
    public function __construct(string $message = 'El cupón ha expirado.')
    {
        parent::__construct($message, 422);
    }
}
