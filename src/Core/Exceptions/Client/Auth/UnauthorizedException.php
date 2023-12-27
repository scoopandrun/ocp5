<?php

namespace App\Core\Exceptions\Client\Auth;

/**
 * Generic 401 Unauthorized exception.
 * 
 * HTTP status = 401
 */
class UnauthorizedException extends AuthException
{
    private const DEFAULT_MESSAGE = "Authentification nécessaire";
    private const HTTP_STATUS = 401;

    public function __construct(string $message = self::DEFAULT_MESSAGE)
    {
        parent::__construct($message, self::HTTP_STATUS);
    }
}
