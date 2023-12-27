<?php

namespace App\Core\Exceptions\Client\Auth;

/**
 * Generic 403 Forbidden exception.
 * 
 * HTTP status = 403
 */
class ForbiddenException extends AuthException
{
    private const DEFAULT_MESSAGE = "Accès interdit";
    private const HTTP_STATUS = 403;

    public function __construct(string $message = self::DEFAULT_MESSAGE)
    {
        parent::__construct($message, self::HTTP_STATUS);
    }
}
