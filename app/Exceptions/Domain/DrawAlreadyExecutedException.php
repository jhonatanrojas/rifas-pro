<?php

namespace App\Exceptions\Domain;

use Exception;

class DrawAlreadyExecutedException extends Exception
{
    public function __construct(string $message = 'El sorteo ya fue ejecutado para esta rifa.')
    {
        parent::__construct($message, 422);
    }
}
