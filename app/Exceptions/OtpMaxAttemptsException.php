<?php

namespace App\Exceptions;

use Exception;

class OtpMaxAttemptsException extends Exception
{
    public function __construct(string $message = '', int $code = 429)
    {
        parent::__construct($message, $code);
    }
}
