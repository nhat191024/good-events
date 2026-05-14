<?php

namespace App\Exceptions;

use Exception;

class OtpCooldownException extends Exception
{
    public function __construct(string $message = '', int $code = 429)
    {
        parent::__construct($message, $code);
    }
}
