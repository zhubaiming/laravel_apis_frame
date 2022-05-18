<?php

namespace ApisFrame\Exceptions\Jwt;

use Exception;
use Throwable;

class JwtException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
