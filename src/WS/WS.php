<?php

namespace Emit\WS;

class WS
{
    private static $consts = null;

    // Frame Opcode
    const FRAME_OPCODE_CONT   = 0x00;
    const FRAME_OPCODE_TEXT   = 0x01;
    const FRAME_OPCODE_BINARY = 0x02;
    const FRAME_OPCODE_CLOSE  = 0x08;
    const FRAME_OPCODE_PING   = 0x09;
    const FRAME_OPCODE_PONG   = 0x0a;

    const FRAME_OPCODE_ARR = [
        self::FRAME_OPCODE_CONT, self::FRAME_OPCODE_TEXT, self::FRAME_OPCODE_BINARY,
        self::FRAME_OPCODE_CLOSE, self::FRAME_OPCODE_PING, self::FRAME_OPCODE_PONG];

    public static function isValidFrameOpcode($opcode)
    {
        return in_array($opcode, self::FRAME_OPCODE_ARR);
    }


    // Close Status Code
    // https://tools.ietf.org/html/rfc6455#section-7.4.1
    const FRAME_CLOSE_NORMAL           = 1000;
    const FRAME_CLOSE_GOINGAWAY        = 1001;
    const FRAME_CLOSE_PROTOCOL_ERROR   = 1002;
    const FRAME_CLOSE_CANNOT_ACCEPT    = 1003;
    const FRAME_CLOSE_RESERVED         = 1004;
    const FRAME_CLOSE_WRONG_ENCODING   = 1007;
    const FRAME_CLOSE_POLICYVIOLATION  = 1008;
    const FRAME_CLOSE_MSG_TOO_BIG      = 1009;
    const FRAME_CLOSE_HANDSHAKE_FAILED = 1010;
    const FRAME_CLOSE_UNEXPECTED       = 1011;

    const FRAME_CLOSE_ARR = [
        self::FRAME_CLOSE_NORMAL, self::FRAME_CLOSE_GOINGAWAY, self::FRAME_CLOSE_PROTOCOL_ERROR,
        self::FRAME_CLOSE_CANNOT_ACCEPT, self::FRAME_CLOSE_RESERVED, self::FRAME_CLOSE_WRONG_ENCODING,
        self::FRAME_CLOSE_POLICYVIOLATION, self::FRAME_CLOSE_MSG_TOO_BIG, 
        self::FRAME_CLOSE_HANDSHAKE_FAILED, self::FRAME_CLOSE_UNEXPECTED
    ];

    public static function isDefinedFrameClose($opcode)
    {
        return in_array($opcode, self::FRAME_CLOSE_ARR);
    }

    public static function getConstName(string $prefix, $targetValue)
    {
        if( is_null( self::$consts ) )
        {
            $class = new \ReflectionClass("Emit\WS\WS");
            self::$consts = $class->getConstants();       
        }

        foreach( self::$consts as $const => $value )
        {
            if( 0 === strpos($const, $prefix) && $value == $targetValue )
            {
                return $const;
            }
        }

        return null;
    } 
}









