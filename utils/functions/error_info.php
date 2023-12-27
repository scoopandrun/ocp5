<?php

require_once __DIR__ . "/array_stringify.php";

/**
 * Gets all the info from an Exception.
 * 
 * @param Throwable $e      Exception.
 * @param string    $format Output format (`array` or `string`).
 * 
 * @return mixed Exception information.
 */
function error_info(?Throwable $e, string $format = "array"): mixed
{
  if (!($e instanceof Throwable)) {
    return null;
  }

  $array_error = [
    "code" => $e->getCode(),
    "message" => $e->getMessage(),
    "file" => $e->getFile(),
    "line" => $e->getLine(),
    "previous" => error_info($e->getPrevious()),
    "trace" => $e->getTrace()
  ];

  $string_error = array_stringify($array_error);

  switch ($format) {
    case 'array':
      return $array_error;

    case 'string':
      return $string_error;

    default:
      return $array_error;
  }
}
