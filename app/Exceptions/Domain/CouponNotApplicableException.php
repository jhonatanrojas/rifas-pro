<?php

namespace App\Exceptions\Domain;

use Exception;

class CouponNotApplicableException extends Exception
{
    public function __construct(string $message = 'El cupón no aplica para esta rifa.')
    {
        parent::__construct($message, 422);
    }
}
