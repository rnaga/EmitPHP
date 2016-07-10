<?php

namespace Emit\FCGI;

use Emit\StreamSocket;

define( 'FCGI_VERSION_1', 1 );
define( 'FCGI_MAX_LENGTH',  hexdec(0xffff) );
define( 'FCGI_KEEP_CONN',  1 );

function FCGIUnpack( $f, $d )
{
    $r = unpack( $f, $d );
    return $r[1];
}

function FCGIPack( $f, $d )
{
    return pack( $f, $d );
}


final class FCGIGetValues
{
    public static function get($key)
    {
        switch($key)
        {
            case 'FCGI_MAX_CONNS':
            case 'FCGI_MAX_REQS':
                return 512;
            case 'FCGI_MPXS_CONNS':
                return 1;
        }

        return null;
    }
}

final class FCGIRole
{
    const FCGI_RESPONDER  = 1;
    const FCGI_AUTHORIZER = 2;
    const FCGI_FILTER     = 3;
}

final class FCGIRequestType
{
    const FCGI_BEGIN_REQUEST      =  1; /* [in]                              */
    const FCGI_ABORT_REQUEST      =  2; /* [in]  (not supported)             */
    const FCGI_END_REQUEST        =  3; /* [out]                             */
    const FCGI_PARAMS             =  4; /* [in]  environment variables       */
    const FCGI_STDIN              =  5; /* [in]  post data                   */
    const FCGI_STDOUT             =  6; /* [out] response                    */
    const FCGI_STDERR             =  7; /* [out] errors                      */
    const FCGI_DATA               =  8; /* [in]  filter data (not supported) */
    const FCGI_GET_VALUES         =  9; /* [in]                              */
    const FCGI_GET_VALUES_RESULT  = 10;  /* [out]                             */
}

final class FCGIProtocolStatus
{
    const FCGI_REQUEST_COMPLETE   = 0;
    const FCGI_CANT_MPX_CONN      = 1;
    const FCGI_OVERLOADED         = 2;
    const FCGI_UNKNOWN_ROLE       = 3;
}


final class FCGIHeader
{
    //unsigned char
    var $version;
    var $type;
    var $requestIdB1;
    var $requestIdB0;
    var $contentLengthB1;
    var $contentLengthB0;
    var $paddingLength;
    var $reserved;

    public static function sizeof(){ return 8; }

    public static function map( $data )
    {
        $o = new FCGIHeader( );
        $o->version = FCGIUnpack( 'C', $data[0] );
        $o->type = FCGIUnpack( 'C', $data[1] );
        $o->requestIdB1 = FCGIUnpack( 'C', $data[2] );
        $o->requestIdB0 = FCGIUnpack( 'C', $data[3] );
        $o->contentLengthB1 = FCGIUnpack( 'C', $data[4] );
        $o->contentLengthB0 = FCGIUnpack( 'C', $data[5] );
        $o->paddingLength = FCGIUnpack( 'C', $data[6] );
        $o->reserved = FCGIUnpack( 'C', $data[7] );

        return $o;
    }  

    public static function extract( $o )
    {
        $data = array();
        $data[0] = FCGIPack( 'C', $o->version );
        $data[1] = FCGIPack( 'C', $o->type );
        $data[2] = FCGIPack( 'C', $o->requestIdB1 );
        $data[3] = FCGIPack( 'C', $o->requestIdB0 );
        $data[4] = FCGIPack( 'C', $o->contentLengthB1 );
        $data[5] = FCGIPack( 'C', $o->contentLengthB0 );
        $data[6] = FCGIPack( 'C', $o->paddingLength );
        $data[7] = FCGIPack( 'C', $o->reserved );

        return join( "", $data );
    }

}

final class FCGIBeginRequest 
{
    var /*unsigned char*/ $roleB1;
    var /*unsigned char*/ $roleB0;
    var /*unsigned char*/ $flags;
    var /*unsigned char*/ $reserved/*[5]*/;

   public static function sizeof(){ return 8;}
   public static function map( $data )
   {
       $o = new FCGIBeginRequest( );
       $o->roleB1 = FCGIUnpack( 'C', $data[0] );
       $o->roleB0 = FCGIUnpack( 'C', $data[1] );
       $o->flags  = FCGIUnpack( 'C', $data[2] );
       $o->reserved = FCGIUnpack( 'C*', $data[3] . $data[4] . $data[5] . $data[6] . $data[7] );

       return $o;
   }
}

final class FCGIBeginRequestRec 
{
    var /*fcgi_header*/ $hdr;
    var /*fcgi_begin_request*/ $body;
 
    public static function sizeof(){ return FCGIHeader::sizeof() + FCGIBeginRequest::sizeof(); }
}

final class FCGIEndRequest 
{
    var /*unsigned char*/ $appStatusB3;
    var /*unsigned char*/ $appStatusB2;
    var /*unsigned char*/ $appStatusB1;
    var /*unsigned char*/ $appStatusB0;
    var /*unsigned char*/ $protocolStatus;
    var /*unsigned char*/ $reserved/*[3]*/;

    public static function sizeof(){ return 8; }

    public static function map( $data )
    {
        $o = new FCGIEndRequest( );
        $o->appStatusB3 = FCGIUnpack( 'C', $data[0] );
        $o->appStatusB2 = FCGIUnpack( 'C', $data[1] );
        $o->appStatusB1 = FCGIUnpack( 'C', $data[2] );
        $o->appStatusB0 = FCGIUnpack( 'C', $data[3] );
        $o->protocolStatus = FCGIUnpack( 'C', $data[4] );
        $o->reserved = FCGIUnpack( 'C', $data[5] . $data[6] . $data[7] );

        return $o;
    }

    public static function extract( $o )
    {
        $data = array();

        $data[0] = $o->appStatusB3;
        $data[1] = $o->appStatusB2;
        $data[2] = $o->appStatusB1;
        $data[3] = $o->appStatusB0;
        $data[4] = $o->protocolStatus;

        for( $i = 5; $i < 8; $i++ )
        {
            $data[$i] = $o->reserved[($i-5)];
        }

        return join( "", $data );
    }
} 

final class FCGIEndRequestRec 
{
    var /*fcgi_header*/ $hdr;
    var /*fcgi_end_request*/ $body;

   function __construct()
   {
       $this->body = new FCGIEndRequest();
   }

   public static function sizeof(){ return FCGIHeader::sizeof() + FCGIBeginRequest::sizeof(); }

   public static function map( $data )
   {
       $data_hdr = substr( $data, 0, FCGIHeader::sizeof( ) );
       $data_body = substr( $data, FCGIHeader::sizeof( ) + 1, FCGIEndRequest::sizeof( ) );

       $o = new FCGIEndRequestRec( );
       $o->hdr = FCGIHeader::map( $data_hdr );
       $o->body = FCGIEndRequest::map( $data_body );

       return $o;
   }

   public static function extract( $o )
   {
       return FCGIHeader::extract( $o->hdr ) . FCGIEndRequest::extract( $o->body );
   }
} 

final class FCGIUtil
{
    public static function initRequest( )
    {
        $req = new FCGI( );
        return $req;
    }

    private static function readHeader($resource, FCGIHeader $hdr )
    {
        //memset
 //       $data = pack( 'C*', 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00 );
 
        $data = StreamSocket::read( $resource, FCGIHeader::sizeof() ); 
       
        if( strlen( $data ) < 8 ) 
        {
            return 0;
        }
 
        $hdr->version = FCGIUnpack( 'C', $data[0] );
        $hdr->type    = FCGIUnpack( 'C', $data[1] );
        $hdr->requestIdB1 = FCGIUnpack( 'C', $data[2] );
        $hdr->requestIdB0 = FCGIUnpack( 'C', $data[3] ); 
        $hdr->contentLengthB1= FCGIUnpack( 'C', $data[4] );
        $hdr->contentLengthB0= FCGIUnpack( 'C', $data[5] );
        $hdr->paddingLength= FCGIUnpack( 'C', $data[6] );
        $hdr->reserved= FCGIUnpack( 'C', $data[7] );

        return 1;
    }

    private static function getParams(FCGI &$req, string $p, int $plen)
    {
        /*char*/ $buf/*[128]*/;
        $buf_size = 128;
        $name_len; $val_len;
        $s;
        $ret = 1;
    
        for( $i = 0; $i < $plen;) {

            $name_len = 0xff & FCGIUnpack( 'C', $p[$i++] );
            if( $name_len >= 128) {
                $name_len = (($name_len & 0x7f) << 24);
                $name_len |= ( FCGIUnpack( 'C', $p[$i++] ) << 16);
                $name_len |= ( FCGIUnpack( 'C', $p[$i++] ) << 8);
                $name_len |= FCGIUnpack( 'C', $p[$i++] );
            }

            $val_len = 0xff & FCGIUnpack( 'C', $p[$i++] );
            if ($val_len >= 128) {
                $val_len = (($val_len & 0x7f) << 24);
                $val_len |= ( FCGIUnpack( 'C', $p[$i++] ) << 16);
                $val_len |= ( FCGIUnpack( 'C', $p[$i++] ) << 8);
                $val_len |= FCGIUnpack( 'C', $p[$i++] );
            }

            if( $name_len + $val_len < 0 ||
                $name_len + $val_len > $plen - $i) {
                /* Malformated request */
                $ret = 0;
                break;
            }
            if ($name_len+1 >= $buf_size) {
                $buf_size = $name_len + 64;
                //tmp = (tmp == buf ? emalloc(buf_size): erealloc(tmp, buf_size));
            }
            
            $tmp = substr( $p, $i, $name_len );
            $s = substr( $p, $i + $name_len, $val_len ); //estrndup((char*)p + name_len, val_len);
            $req->env[$tmp] = $s;
            $i += $name_len + $val_len;
        }

        return $ret;
    }


    public static function readRequest($resource, FCGI $req )
    {
        if( !is_resource( $resource ) )
        {
            return array(0, "Passing invalid resource");
        }

        $hdr = new FCGIHeader( );

        $len = $padding = 0;

        $req->keep = 0;
        $req->closed = 0;
        $req->inLen = 0;
        $req->outHdr = null;

        self::readHeader( $resource, $hdr );

        $len = ($hdr->contentLengthB1 << 8) | $hdr->contentLengthB0;
        $padding = $hdr->paddingLength; 

        while( $hdr->type == FCGIRequestType::FCGI_STDIN && $len == 0 ) 
        {
            if( 0 == self::readHeader( $resource, $hdr ) || $hdr->version < FCGI_VERSION_1 ) 
            {
                //Logs::error( 'Fcgi Error: invliad header or version');
                return array(0, 'Fcgi Error: invliad header or version');
            }
            
            $len = ($hdr->contentLengthB1 << 8) | $hdr->contentLengthB0;
            $padding = $hdr->paddingLength;
        }

        if( $len + $padding > FCGI_MAX_LENGTH ) 
        {
            //Logs::error( 'Fcgi Error: content length too long');
            return array(0, 'Fcgi Error: content length too long. ' . ($len + $padding));
        }

        $req->id = ($hdr->requestIdB1 << 8) + $hdr->requestIdB0;

        if( $hdr->type == FCGIRequestType::FCGI_BEGIN_REQUEST && 
            $len == FCGIBeginRequest::sizeof() ) 
        {
            $val = "";

            $buf = StreamSocket::read( $resource, $len + $padding );
     
            if( strlen( $buf ) != $len + $padding ) 
            {
                //Logs::error( 'Fcgi Error: invalid content length');
                return array(0, 'Fcgi Error: invalid content length');
            }

            $fbr = FCGIBeginRequest::map( $buf );

            $req->keep = ( $fbr->flags & FCGI_KEEP_CONN );

            switch( ( $fbr->roleB1 << 8) + ( $fbr->roleB0 ) ) 
            {
                case FCGIRole::FCGI_RESPONDER:
                    $req->env["FCGI_ROLE"] = "RESPONDER";
                    break;
                case FCGIRole::FCGI_AUTHORIZER:
                    $req->env["FCGI_ROLE"] = "AUTHORIZER";
                    break;
                case FCGIRole::FCGI_FILTER:
                    $req->env["FCGI_ROLE"] = "FILTER";
                    break;
                default:
                    //Logs::error( 'Fcgi Error: invalid fcgi role');
                    return array(0, 'Fcgi Error: invalid fcgi role');
            }

            if( 0 == self::readHeader( $resource, $hdr ) || $hdr->version < FCGI_VERSION_1 ) 
            {
                //Logs::error( 'Fcgi Error: invalid header or version 2');
                return array(0, 'Fcgi Error: invalid header or version 2');
            }

            $len = ($hdr->contentLengthB1 << 8) | $hdr->contentLengthB0;
            $padding = $hdr->paddingLength;

            while( $hdr->type == FCGIRequestType::FCGI_PARAMS && $len > 0 ) 
            {
                if ($len + $padding > FCGI_MAX_LENGTH ) 
                {
                    //Logs::error( 'Fcgi Error: content length too long 2');
                    return array(0, 'Fcgi Error: content length too long 2');
                }

                $buf = StreamSocket::read( $resource, $len + $padding );

                if( strlen( $buf ) != $len + $padding ) 
                {
                    $req->keep = 0;
                    //Logs::error( 'Fcgi Error: invalid content length');
                    return array(0, 'Fcgi Error: invalid content length');
                }

                if( !self::getParams( $req, $buf, strlen($buf) ) )
                {
                    $req->keep = 0;
                    //Logs::error( 'Fcgi Error: invalid parameters');
                    return array(0, 'Fcgi Error: invalid parameters');
                }

                if( 0 == self::readHeader( $resource, $hdr ) || $hdr->version < FCGI_VERSION_1 ) 
                {
                    $req->keep = 0;
                    //Logs::error( 'Fcgi Error: invalid header or version 2');
                    return array(0, 'Fcgi Error: invalid header or version 2');
                }
            
                $len = ($hdr->contentLengthB1 << 8) | $hdr->contentLengthB0;
                $padding = $hdr->paddingLength;
            }

        }
        else if ($hdr->type == FCGIRequestType::FCGI_GET_VALUES ) 
        {
            $buf = StreamSocket::read( $resource, $len + $padding );

            if( strlen( $buf ) != $len + $padding )
            {
                $req->keep = 0;
                //Logs::error( 'Fcgi Error: invalid content length 3');
                return array(0, 'Fcgi Error: invalid content length 3');
            }

            if( !self::getParams( $req, $buf, strlen($buf) ) )
            {
                $req->keep = 0;
                //Logs::error( 'Fcgi Error: invalid parameters 2');
                return array(0, 'Fcgi Error: invalid parameters 2');
            }           

            if( count( $req->env ) )
            {
                $i = 0;
                foreach( $req->env as $key => $value )
                {
                    if( strlen( $value = FCGIGetValues::get($key) ) > 0 )
                    {
                        $str_length = strlen( $key );

                        if( $str_length < 0x80 ) 
                        {
                            $buf[$i++] = FCGIPack( 'C', $str_length );
                        } 
                        else 
                        {
                            $buf[$i++] = FCGIPack( 'C', (($str_length >> 24) & 0xff) | 0x80 );
                            $buf[$i++] = FCGIPack( 'C', ($str_length >> 16) & 0xff );
                            $buf[$i++] = FCGIPack( 'C', ($str_length >> 8) & 0xff );
                            $buf[$i++] = FCGIPack( 'C', $str_length & 0xff );
                        }

                        $zlen = strlen( $value );

                        if( $zlen < 0x80 ) 
                        {
                            $buf[$i++] = $zlen;
                        } 
                        else 
                        {
                            $buf[$i++] = FCGIPack( 'C', (($zlen >> 24) & 0xff) | 0x80 );
                            $buf[$i++] = FCGIPack( 'C', ($zlen >> 16) & 0xff );
                            $buf[$i++] = FCGIPack( 'C', ($zlen >> 8) & 0xff );
                            $buf[$i++] = FCGIPack( 'C', $zlen & 0xff );
                        }

                        $buf .= $key . $value;
                    }
                }

                $buf .= self::makeHeader( $hdr, FCGIRequestType::FCGI_GET_VALUES_RESULT, 0, strlen( $buf ) );
                $buf = FCGIHeader::extract( $hdr ) . $buf;

                if( StreamSocket::write( $resource, $buf, strlen( $buf ) ) != strlen( $buf ) )
                {
                    //Logs::error( 'Fcgi Error: socket write failed');
                    $req->keep = 0;
                } 
 
                //Logs::error( 'Fcgi Error: FCGI_GET_VALUES called');
                return array(0, 'Fcgi Error: FCGI_GET_VALUES called');
            }

            //Logs::error( 'Fcgi Error: FCGI_GET_VALUES called without env');
            return array(0, 'Fcgi Error: FCGI_GET_VALUES called without env');
        }
        else
        {

            //Logs::error( 'Fcgi Error: unknown request type:' . $hdr->type );
            return array(0, 'Fcgi Error: unknown request type:' . $hdr->type);
        }

        return array( 1, null );
    }

    public static function read($resource, FCGI $req, &$str, int $len = 65536 )
    {
        if( !is_resource( $resource ) )
        {
            return 0;
        }

        $n = 0;
        $rest = $len;
        $hdr = new FCGIHeader( );

        while( $rest > 0 ) 
        {
            if( $req->inLen == 0 ) 
            {
                if( 0 == self::readHeader( $resource, $hdr ) || $hdr->version < FCGI_VERSION_1 ||
                    $hdr->type != FCGIRequestType::FCGI_STDIN) 
                {
                    $req->keep = 0;
                    return 0;
                }

                $req->inLen = ($hdr->contentLengthB1 << 8) | $hdr->contentLengthB0;
                $req->inPad = $hdr->paddingLength;

                if( $req->inLen == 0 ) 
                {
                    return $n;
                }
            }
    
            if( $req->inLen >= $rest ) 
            {
                $tmp_str = StreamSocket::read( $resource, $rest);
                $ret = strlen( $tmp_str );
            } 
            else 
            {
                $tmp_str = StreamSocket::read( $resource, $req->inLen );
                $ret = strlen( $tmp_str );
            }

            if( $ret < 0) 
            {
                $req->keep = 0;
                return $ret;
            } 
            else if( $ret > 0 ) 
            {
                $req->inLen -= $ret;
                $rest -= $ret;
                $n += $ret;
                $str = $str.$tmp_str;

                if( $req->inLen == 0 ) 
                {
                    if( $req->inPad ) 
                    {
                        $buf = StreamSocket::read( $resource, $req->inPad );

                        if( strlen( $buf ) != $req->inPad) 
                        {
                            $req->keep = 0;
                            return $ret;
                        }
                    }

                } 
                else 
                {
                    return $n;
                }

            } 
            else 
            {
                return $n;
            }
        }

        return $n;
    }

    private static function makeHeader( &$hdr, $type, int $req_id, int $len )
    {
        $paddingLength = (($len + 7) & ~7) - $len;
        $pad = array();
  
        $hdr = new FCGIHeader( );

        $hdr->contentLengthB0 = ($len & 0xff);
        $hdr->contentLengthB1 = (($len >> 8) & 0xff);
        $hdr->paddingLength = $paddingLength;
        $hdr->requestIdB0 = ($req_id & 0xff);
        $hdr->requestIdB1 = (($req_id >> 8) & 0xff);
        $hdr->reserved = 0x00;
        $hdr->type = $type;
        $hdr->version = FCGI_VERSION_1;

        if( $paddingLength )
        {
            for( $i = 0; $i < $paddingLength; $i++ )
            {
                $pad[] = FCGIPack( 'C', 0x00 );
            }
        }

        return join("",$pad);
    }

    private static function openPacket(FCGI $req, $type )
    {
        $req->outHdr = new FCGIHeader( );
        $req->outHdr->type = $type;

        return $req->outHdr;
    }
    
    private static function closePacket(FCGI $req )
    {
        if( $req->outHdr != null ) 
        {
            $pad = self::makeHeader( $req->outHdr, $req->outHdr->type, $req->id, strlen( $req->outBuf ) );
            $req->outBuf = FCGIHeader::extract( $req->outHdr ) . $req->outBuf . $pad; 
            unset($req->outHdr);
            $req->outHdr = NULL;
        }
    }

    public static function flush($resource, FCGI $req, $close )
    {
        if( !is_resource( $resource ) )
        {
            return 0;
        }

        self::closePacket( $req );
        $len = strlen( $req->outBuf );
   
        if( $close ) 
        {
            $rec = new FCGIEndRequestRec();
    
            self::makeHeader( $rec->hdr, FCGIRequestType::FCGI_END_REQUEST, 
                              $req->id, FCGIEndRequest::sizeof() );

            $rec->body->appStatusB3 = FCGIPack( 'C', 0x00 );
            $rec->body->appStatusB2 = FCGIPack( 'C', 0x00 );
            $rec->body->appStatusB1 = FCGIPack( 'C', 0x00 );
            $rec->body->appStatusB0 = FCGIPack( 'C', 0x00 );
            $rec->body->protocolStatus = FCGIPack( 'C', FCGIProtocolStatus::FCGI_REQUEST_COMPLETE);

            for( $i = 0; $i < 3; $i++ )
            {
                $rec->body->reserved[$i] = FCGIPack( 'C', 0x00 );
            }

            $req->outBuf .= FCGIEndRequestRec::extract( $rec );

            $len += FCGIEndRequestRec::sizeof( );

            unset( $rec );
        }
    
        $n = StreamSocket::write( $resource, $req->outBuf, strlen( $req->outBuf ) );

        if ( $n != $len ) 
        {
            $req->keep = 0;
            return 0;
        }
    
        unset( $req->outBuf );
        $req->outBuf = null;
        return 1;
    }

    public static function write($resource, FCGI $req, $type, $str, int $len )
    {
        $buflen = 16384;

        if( $len <= 0 ) 
        {
            return 0;
        }
    
        if( $req->outHdr && $req->outHdr->type != $type ) 
        {
            self::closePacket( $req );
        }
    
        $rest = $len;

        while( $rest > 0 ) 
        {
            $limit = $buflen - strlen( $req->outBuf );
  
            if( $req->outHdr == null ) 
            {
                if( $limit < FCGIHeader::sizeof() ) 
                {
                    if ( !self::flush( $resource, $req, 0 ) ) 
                    {
                        return -1;
                    }
                }

                self::openPacket( $req, $type );
            }

            $limit = $buflen - strlen($req->outBuf);

            if( $rest < $limit ) 
            {
                //we have space in buffer
                $req->outBuf .= $str;
                return $len;
            } 
            else 
            {
                //no more buffer. send it
                $req->outBuf .= substr( $str, 0, $limit );
                $rest -= $limit;
                $str = substr( $str, 0, $limit);

                if (!self::flush( $resource, $req, 0 ) ) 
                {
                    return -1;
                }
            }
        }   

        return $len;
    }

    public static function finishRequest($resource, FCGI $req, $force_close )
    {
        $ret = 1;
        if( is_null($req) || !is_resource( $resource ) ) return false;

        if( (int)( $resource ) >= 0 ) 
        {
            if(! $req->closed ) 
            {
                $ret = self::flush( $resource, $req, 1 );
                $req->closed = 1;
            }

            //self::close( $req, $force_close, 1 );
        }

        return $ret;
    }
}
