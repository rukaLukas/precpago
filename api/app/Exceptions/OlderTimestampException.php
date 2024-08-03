<?php

namespace App\Exceptions;
use Exception;

class OlderTimestampException extends Exception
{
    public function __construct($message = "Transação mais antiga que 60 segundos.", $code = 204, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}