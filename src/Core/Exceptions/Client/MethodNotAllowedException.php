<?php

namespace App\Core\Exceptions\Client;

use App\Core\Exceptions\Client\ClientException;

/**
 * Method not allowed exception.
 * 
 * HTTP status = 405
 */
class MethodNotAllowedException extends ClientException
{
    private const DEFAULT_MESSAGE = "Method Not Allowed";
    private const HTTP_STATUS = 405;

    public function __construct(
        string $message = self::DEFAULT_MESSAGE,
        public int $httpStatus = self::HTTP_STATUS,
        \Throwable|null $previous = null
    ) {
        parent::__construct($message, $httpStatus, $previous);
    }
}
