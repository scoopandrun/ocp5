<?php

namespace App\Core;

use Throwable;
use Twig\Error\SyntaxError as TwigSyntaxError;

/**
 * Custom error logger.
 */
class ErrorLogger
{
    private string|null $emergencyMemory = null;
    private array $originalError = [];
    const ERROR_INFO_MAX_DEPTH = 10;

    /**
     * @param Throwable $error Error
     */
    public function __construct(public Throwable $error)
    {
        $this->emergencyMemory = str_repeat("*", 1024 * 1024);
    }

    /**
     * Log an error.
     */
    public function log(): void
    {
        $this->originalError = ErrorLogger::errorInfo($this->error, maxDepth: 0);

        register_shutdown_function([$this, "emergencyShutdown"]);

        $error_string = ErrorLogger::errorInfo($this->error, "string");

        error_log(PHP_EOL . $error_string);
    }

    /**
     * Stringify an array
     * 
     * @param array $array       Array to be stringified
     * @param int   $indentation Indentation spaces for the string output
     * 
     * @return string Stringified array
     */
    public static function arrayStringify(array $array, int $indentation = 0): string
    {
        $string = "";
        $indentation_spaces = str_repeat(" ", $indentation);

        foreach ($array as $key => $value) {
            // If $value is an array, recursive stringification
            if (is_array($value)) {
                $value = "[" . PHP_EOL . ErrorLogger::arrayStringify($value, $indentation + 2) . str_repeat(" ", $indentation) . "]";
            }

            if (is_object($value)) {
                $value = print_r($value, true);
            }

            $string .= $indentation_spaces . "$key => $value" . PHP_EOL;
        }

        return $string;
    }

    /**
     * Gets all the info from an Exception.
     * 
     * @param Throwable $e      Exception.
     * @param string    $format Output format (`array` or `string`).
     * 
     * @return mixed Exception information.
     */
    public static function errorInfo(
        ?Throwable $e,
        string $format = "array",
        int $maxDepth = ErrorLogger::ERROR_INFO_MAX_DEPTH
    ): mixed {
        if (!($e instanceof Throwable)) {
            return null;
        }

        if ($maxDepth < 0) {
            return "Max depth reached";
        }

        $array_error = [
            "code" => $e->getCode(),
            "message" => $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine(),
            "previous" => ErrorLogger::errorInfo($e->getPrevious(), maxDepth: $maxDepth - 1),
            // Twig stack trace is too long and causes OOM crash. Do not include in error log
            "trace" => $e instanceof TwigSyntaxError ? null : $e->getTrace(),
        ];

        $string_error = ErrorLogger::arrayStringify($array_error);

        switch ($format) {
            case 'array':
                return $array_error;

            case 'string':
                return $string_error;

            default:
                return $array_error;
        }
    }

    /**
     * Emergency error logger in case of a catastrophic exception
     * (eg: out of memory).
     */
    private function emergencyShutdown(): void
    {
        // Free the emergency memory
        $this->emergencyMemory = null;

        $lastError = error_get_last();

        if ($lastError && !in_array($lastError["type"], [E_NOTICE, E_WARNING])) {
            error_log(
                PHP_EOL .
                    "=== Emergency shutdown ==="
                    . PHP_EOL
                    . ErrorLogger::arrayStringify($this->originalError)
                    . PHP_EOL
            );
        }
    }
}
