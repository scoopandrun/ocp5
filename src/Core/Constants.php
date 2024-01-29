<?php

namespace App\Core;

class Constants
{
    public static string $ROOT = "";
    public static string $TEMPLATES = "";

    public static function setRoot(string $path): void
    {
        static::$ROOT = $path;
    }

    public static function setTemplates(string $path): void
    {
        static::$TEMPLATES = $path;
    }
}
