<?php

namespace Emit;

class StreamSocket
{
    public static function accept( $fd )
    {
        stream_set_blocking( $fd, 0 );
        $new_socket = stream_socket_accept( $fd ); 
        stream_set_blocking( $fd, 1 );
        return $new_socket;
    }

    public static function create( )
    {
        return socket_create(AF_INET, SOCK_STREAM, 0);
    }
    
    public static function createServerSocket( $host, $port )
    {
        $fd = @stream_socket_server("tcp://$host:$port", $errno, $errstr); 
        return $fd;
    }

    public static function close( &$fd )
    {
        return stream_socket_shutdown( $fd, STREAM_SHUT_WR ); 
    }

    public static function connect( $address, $port )
    {
        $fd = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );

        if( !socket_connect( $fd, $address, $port ) )
        {
            return null;
        }
    }
 
    public static function getRemoteHost($fd, $wantPeer = false)
    {
        return stream_socket_get_name ( $fd, $wantPeer );
    }
 
    public static function readBlocking( &$fd, $length = 0 )
    {
        $data = self::read( $fd, $length, 1 );
        return $data;
    }

    public static function read( &$fd, $length = 8192, $blocking = 0 )
    {
        $data = null;

        if( $blocking == 0 )
        {
            stream_set_blocking( $fd, 0 ); 
        }

        if( $length > 0 )
        {
            $data = stream_socket_recvfrom( $fd, $length );
        }
        else
        {
            for(;;)
            {
                $tmp = stream_socket_recvfrom( $fd, 1024 );
                if( $tmp == false ) break;

                $data .= $tmp;
            }
        }    

        if( $blocking == 0 )
        {
            stream_set_blocking( $fd, 1 );
        }

        if( $data == "" ) return null;
        else return $data;

    }

    public static function writeBlocking( &$fd, $str, $len = 0 )
    {
        return self::write( $fd, $str, $len, 1 );
    }

    public static function write( &$fd, $str, $len = 0, $blocking = 0 )
    {
        $len = ( $len == 0 ) ? strlen( $str ) : $len;
        $r = 0;


        if( $blocking == 0 )
        {
            stream_set_blocking( $fd, 0 );
        }

        do
        {
            $n = stream_socket_sendto( $fd, $str );
            if( $n == false || 0 >= $n ) return -1;
            $len -= $n; $r += $n;
 
        }
        while( $len > 0 );

        if( $blocking == 0 )
        {
            stream_set_blocking( $fd, 1 );
        }
        
        return $r;
    }

    public static function setTimeout( &$fd, $sec )
    {
        stream_set_timeout($fd, $sec );
    }
}
