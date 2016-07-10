<?php

namespace Emit;

use Emit\Config;

class GlobalConfig
{
    private static $config = null;

    private function __construct($arrOrFile){}

    public static function read(array $arr)
    {
        if( !is_null(self::$config) )
            return self::$config;

        self::$config = Config::read($arr);
        return self::$config;
    }

    public static function readFile(string $arr, string $var)
    {
        if( !is_null(self::$config) )
            return self::config;

        self::$config = Config::readFile($arr, $var);
        return self::$config;
    }

    public static function get($key)
    {
        if( !is_null(self::$config) )
            return null;

        return self::$config->get($key);
    }
}
