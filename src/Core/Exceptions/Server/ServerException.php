<?php

namespace App\Core\Exceptions\Server;

use App\Core\Exceptions\AppException;

/**
 * Generic server exception.
 * 
 * HTTP status = 500
 */
class ServerException extends AppException
{
    private const DEFAULT_MESSAGE = "Erreur serveur";
    private const HTTP_STATUS = 500;

    public function __construct(
        string $message = self::DEFAULT_MESSAGE,
        public int $httpStatus = self::HTTP_STATUS,
        \Throwable|null $previous = null
    ) {
        parent::__construct($message, $httpStatus, $previous);
    }
}
