<?php

namespace App\Core\Exceptions\Client;

use App\Core\Exceptions\AppException;

/**
 * Generic bad request exception.
 * 
 * HTTP status = 400
 */
class ClientException extends AppException
{
    private const DEFAULT_MESSAGE = "Erreur de requête";
    private const HTTP_STATUS = 400;

    public function __construct(
        string $message = self::DEFAULT_MESSAGE,
        public int $httpStatus = self::HTTP_STATUS,
        \Throwable|null $previous = null
    ) {
        parent::__construct($message, $httpStatus, $previous);
    }
}
