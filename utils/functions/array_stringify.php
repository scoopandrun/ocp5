<?php

/**
 * Stringify an array
 * 
 * @param array $array       Array to be stringified
 * @param int   $indentation Indentation spaces for the string output
 * 
 * @return string Stringified array
 */
function array_stringify(array $array, int $indentation = 0): string
{
  $string = "";
  $indentation_spaces = str_repeat(" ", $indentation);

  foreach ($array as $key => $value) {
    // If $value is an array, recursive stringification
    if (gettype($value) === "array") {
      $value = "[" . PHP_EOL . array_stringify($value, $indentation + 2) . str_repeat(" ", $indentation) . "]";
    }

    if (is_object($value)) {
      $value = print_r($value, 1);
    }

    $string .= $indentation_spaces . "$key => $value" . PHP_EOL;
  }

  return $string;
}
