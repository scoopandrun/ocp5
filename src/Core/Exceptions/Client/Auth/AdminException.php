<?php

namespace App\Core\Exceptions\Client\Auth;

/**
 * Exception thrown when non-admin user tries to access
 * an admin-restricted resource.
 */
class AdminException extends ForbiddenException
{
    private const DEFAULT_MESSAGE = "L'utilisateur n'est pas administrateur";

    public function __construct(string $message = self::DEFAULT_MESSAGE)
    {
        parent::__construct($message);
    }
}
