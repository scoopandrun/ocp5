<?php

namespace App\Core\Exceptions\Server\DB;

use App\Core\Exceptions\Server\ServerException;

/**
 * Generic database exception.
 * 
 * HTTP status = 500
 */
class DBException extends ServerException
{
    private const DEFAULT_MESSAGE = "Erreur générique de la base de données";
    private const HTTP_STATUS = 500;

    public function __construct(
        string $message = self::DEFAULT_MESSAGE,
        public int $http_status = self::HTTP_STATUS,
        \Throwable|null $previous = null
    ) {
        parent::__construct($message, $http_status, $previous);
    }
}
