<?php

namespace App\Core\Exceptions\Client\Auth;

/**
 * Exception thrown when the user session doesn't exist.
 */
class SessionException extends UnauthorizedException
{
    private const DEFAULT_MESSAGE = "La session n'existe pas ou a expiré";

    public function __construct(string $message = self::DEFAULT_MESSAGE)
    {
        parent::__construct($message);
    }
}
