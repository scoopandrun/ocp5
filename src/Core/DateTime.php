<?php

namespace App\Core;

class DateTime extends \DateTime implements \Stringable
{
    public function __construct(public string $date = "now")
    {
        parent::__construct($date);
    }

    public function __toString(): string
    {
        return date("Y-m-d H:i:s");
    }
}
