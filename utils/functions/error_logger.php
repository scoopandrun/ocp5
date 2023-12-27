<?php

require_once __DIR__ . "/error_info.php";

/**
 * Custom error logger.
 * 
 * @param Throwable $e Error
 */
function error_logger(Throwable $e)
{
  $error_string = error_info($e, "string");

  error_log(PHP_EOL . $error_string);
}
