<?php

namespace Emit\WS;

use Emit\StreamSocket;

class WSUtil
{
    public static function initRequest()
    {
        return new WSServerRequest();
    }

    public static function initFrame()
    {
        return new WSFrame();
    }
 
    private static function unpack( $f, $d )
    {
        $r = unpack( $f, $d );
        return $r[1];
    }

    private static function pack( $f, $d )
    {
        return pack( $f, $d );
    }

    public static function packPing(WSFrame $frame)
    {
        $frame->opcode = WS::FRAME_OPCODE_PING;
        return self::packFrame($frame, "");
    }

    public static function packPong(WSFrame $frame)
    {
        $frame->opcode = WS::FRAME_OPCODE_PONG;
        return self::packFrame($frame, "");
    }

    public static function packFrame(WSFrame $frame, $payload)
    {
         $data = "";

         if( !WS::isValidFrameOpcode($frame->opcode) )
         {
             $frame->error = "Invalid opcode " . $frame->opcode;
             return null;
         }

         $opcode = $frame->opcode;
         $fin = $frame->fin << 7;

         // The first byte (Fin, RSV1-3 and Opcode)
         $data .= self::pack('C', $fin | ( 0x0f & $opcode) );

         // The second byte (Mask, Payload Length)
         //
         // https://tools.ietf.org/html/rfc6455#section-5.1
         // A server MUST NOT mask any frames that it sends to the client.  
         // A client MUST close a connection if it detects a masked frame. 
         $binMask = ( $frame->mask == 0x01 ) ? 0x80 : 0x00;

         // Payload
         $payloadLen = strlen($payload);

         // https://tools.ietf.org/html/rfc6455#section-5.5.1
         // If there is a body, the first two bytes of
         // the body MUST be a 2-byte unsigned integer (in network byte order)
         if( $opcode == WS::FRAME_OPCODE_CLOSE )
         {
             $payloadLen += 2;
         }

         if( $payloadLen > 65535 )
         { 
             // == 127 isn't available NOW
             $frame->error = "Payload too long " . $payloadLen;
             return false;
         }
         else if( $payloadLen < 126 )
         {
             $binPayload = 0x7f & ( strlen($payload) );
             $data .= self::pack('C', $binMask | $binPayload );
         }
         else // == 126
         {
             $data .= self::pack('C', $binMask | 0x7e ) // 0x7e => 126
                    // Multibyte length quantities are expressed in network byte order.(Big Endian)
                    . self::pack('n', $payloadLen); // 2bytes, Big Endian
         }

         // Mask payload => This likely doesn't happen. See above
         if( $payloadLen > 0 && $frame->mask == 1 )
         {
              for($i = 0, $maskingKey = ""; $i < 4; $i++)
              {
                  $maskingKey .= self::pack('C', rand(0,255));
              }

              $data .= $maskingKey;

              for( $i = 0; $i < strlen($payload); $i++)
              {
                  $payload[$i] = $payload[$i]^$maskingKey[$i%4];
              }
         }

         if( $opcode == WS::FRAME_OPCODE_CLOSE )
         {
             $data .= self::pack( 'n', $frame->closeStatus );
         }

         $data .= $payload;

         return $data; 
    }

    public static function unpackFrame(WSFrame $frame, $data)
    {
        $data = $frame->buf . $data;

        $payloadOffset = 2;

        $bin = self::unpack( "C", $data[0]);

        $frame->fin = $bin >> 7;

        /*
        $rsv1 = ( $bin >> 6 ) & 0x01;
        $rsv2 = ( $bin >> 5 ) & 0x01;
        $rsv3 = ( $bin >> 4 ) & 0x01;
        */

        $opcode = $frame->opcode = ( 0x0f & $bin );

        if( !WS::isValidFrameOpcode($frame->opcode) )
        {
            $frame->error = "Invalid opcode " . $frame->opcode;
            return false;
        }

        $bin = self::unpack( "C", $data[1]);

        $frame->mask = $bin >> 7 ;
        $payloadLen = 0x7f & $bin;

        if( $payloadLen == 0x7e ) // 126
        {
           $bin16 = substr( $data, 2, 2 );
           $payloadLen = self::unpack( 'n', $bin16 );
           $payloadOffset = 4;
        }
        else if( $frame->payloadLen == 0x7f ) // 127 
        {
            $bin64 = substr( $data, 2, 8 );
            $payloadLen = self::unpack( 'J', $bin64 );
            $payloadOffset = 10;
        }

        if( $payloadLen > ( strlen($data) + $payloadOffset ) )
        {
            $frame->buf = $data;
            return false;
        }

        $frame->payloadLen = $payloadLen;

        if( $payloadLen == 0 )
            return $frame->fin == 1 ? true : false;

        $mask = $frame->mask;

        if( $mask == 1 )
        {
            $frame->maskingKey = $maskingKey = substr($data, $payloadOffset, 4);
            $payloadOffset += 4;
        }

        $payload = substr($data, $payloadOffset, $payloadLen);

        if( $mask == 1 )
        {
            for( $i = 0; $i < strlen($payload); $i++)
            {
                $payload[$i] = $payload[$i]^$maskingKey[$i%4];
            }
        }

        if( $frame->fin == 0 )
        {
            $frame->bufPayload .=  $payload;
            return false;
        }

        $frame->payload = $frame->bufPayload . $payload;

        // 5.5.1.  Close
        //
        // If there is a body, the first two bytes of
        // the body MUST be a 2-byte unsigned integer (in network byte order)
        if( $opcode == WS::FRAME_OPCODE_CLOSE )
        {
            $bin16 = substr( $frame->payload, 0, 2 );

            $frame->isClosing = true;

            $frame->closeStatus = self::unpack( 'n', $bin16 );
            $closeStatusString = WS::getConstName("FRAME_CLOSE", $frame->closeStatus);

            if( !is_null($closeStatusString) )
                $frame->closeStatusString = $closeStatusString;

            $frame->payload = substr($frame->payload, 2);
        }

        return true;
    }

    public static function readFrame($resource, WSFrame $frame)
    {
        $data = StreamSocket::read($resource);

        if( is_null( $data ) )
        {
            $frame->error = "Connection Closed";
            return false;
        }

        return self::unpackFrame($frame, $data);
    }
}
