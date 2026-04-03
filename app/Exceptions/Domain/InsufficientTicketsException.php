<?php

namespace App\Exceptions\Domain;

use Exception;

class InsufficientTicketsException extends Exception
{
    public function __construct(string $message = "No hay suficientes tickets disponibles para la cantidad solicitada.")
    {
        parent::__construct($message, 422);
    }
}
