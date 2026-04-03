<?php

namespace App\Exceptions\Domain;

use Exception;

class TicketAlreadySoldException extends Exception
{
    public function __construct(string $message = 'El ticket ya fue vendido y no está disponible.')
    {
        parent::__construct($message, 422);
    }
}
