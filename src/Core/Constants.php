<?php

namespace App\Core;

class Constants
{
    public static string $ROOT = "";
    public static string $TEMPLATES = "";

    public static function setRoot(string $path)
    {
        static::$ROOT = $path;
    }

    public static function setTemplates(string $path)
    {
        static::$TEMPLATES = $path;
    }
}
