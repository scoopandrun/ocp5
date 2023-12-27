<?php

namespace App\Core\Exceptions\Client\Auth;

/**
 * User login exception.
 */
class LoginException extends UnauthorizedException
{
    private const DEFAULT_MESSAGE = "Utilisateur ou mot de passe incorrect";

    public function __construct(string $message = self::DEFAULT_MESSAGE)
    {
        parent::__construct($message);
    }
}
