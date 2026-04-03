<?php

namespace App\Exceptions\Domain;

use Exception;

class TicketReservationExpiredException extends Exception
{
    public function __construct(string $message = 'La reserva del ticket ha expirado.')
    {
        parent::__construct($message, 422);
    }
}
