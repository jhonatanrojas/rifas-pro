<?php

namespace App\Exceptions\Domain;

use Exception;

class RaffleNotActiveException extends Exception
{
    public function __construct(string $message = 'La rifa no está activa.')
    {
        parent::__construct($message, 422);
    }
}
