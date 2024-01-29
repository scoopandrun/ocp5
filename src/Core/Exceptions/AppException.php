<?php

namespace App\Core\Exceptions;

/**
 * Generic application exception.
 * 
 * All the exceptions thrown by the app MUST extend this class.
 */
abstract class AppException extends \Exception
{
    private const DEFAULT_MESSAGE = "Erreur générique de l'application";
    private const HTTP_STATUS = 500;

    public function __construct(
        string $message = self::DEFAULT_MESSAGE,
        public int $httpStatus = self::HTTP_STATUS,
        \Throwable|null $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
