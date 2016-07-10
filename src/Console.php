<?php

namespace Emit;

class Console
{
    const LOG_ALL    = 0x0f;
    const LOG_DEBUG  = 0x01;
    const LOG_NOTICE = 0x02;
    const LOG_WARN   = 0x04;
    const LOG_ERROR  = 0x08;

    const LOG_TXT = [
        self::LOG_DEBUG  => 'DEBUG',
        self::LOG_NOTICE => 'NOTICE',
        self::LOG_WARN   => 'WARN',
        self::LOG_ERROR  => 'ERROR',
    ];

    private static $level = self::LOG_ALL; 

    public static function setLevel($level)
    {
        self::$level = $level;
    }

    public static function log($msg, $level = self::LOG_NOTICE)
    {
        if( 0 >= ( self::$level & $level ) )
            return;

        $trace = debug_backtrace();

        $ref = $trace[2]['class'] . "::" . $trace[2]['function'];

        $msg = str_replace("\n", "", $msg);
        $msg = date('Y-m-d H:i:s') . "\t" . self::LOG_TXT[$level] . "\t$ref\t$msg\n";

        echo $msg;
    }

    public static function warn($msg)
    {
        self::log($msg, self::LOG_WARN);
    }

    public static function error($msg)
    {
        self::log($msg, self::LOG_ERROR);
    }

    public static function debug($msg)
    {
        self::log($msg, self::LOG_DEBUG);
    }
}




