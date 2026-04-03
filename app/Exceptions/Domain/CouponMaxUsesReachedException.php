<?php

namespace App\Exceptions\Domain;

use Exception;

class CouponMaxUsesReachedException extends Exception
{
    public function __construct(string $message = 'Este cupón ha agotado sus usos permitidos.')
    {
        parent::__construct($message, 422);
    }
}
