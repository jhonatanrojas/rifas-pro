<?php

namespace App\Exceptions\Domain;

use Exception;

class PaymentAlreadyReviewedException extends Exception
{
    public function __construct(string $message = 'El pago ya fue revisado previamente.')
    {
        parent::__construct($message, 422);
    }
}
