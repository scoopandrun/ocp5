<?php

namespace App\Core;

class Security
{
    const LOGIN_FAILURE_TIMEOUT = 2;

    /**
     * Delay before sending the response after a failed login attempt.
     */
    static function preventBruteforce(): void
    {
        sleep(static::LOGIN_FAILURE_TIMEOUT);
    }
}
