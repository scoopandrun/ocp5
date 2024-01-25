<?php

namespace App\Core\Exceptions\Client;

use App\Core\Exceptions\Client\ClientException;

/**
 * Resource not found exception.
 * 
 * HTTP status = 404
 */
class NotFoundException extends ClientException
{
    private const DEFAULT_MESSAGE = "Page non trouvée";
    private const HTTP_STATUS = 404;

    public function __construct(
        string $message = self::DEFAULT_MESSAGE,
        public int $httpStatus = self::HTTP_STATUS,
        \Throwable|null $previous = null
    ) {
        parent::__construct($message, $httpStatus, $previous);
    }
}
