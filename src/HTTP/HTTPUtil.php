<?php

namespace Emit\HTTP;

use Emit\StreamSocket;
use Emit\Console;

if (!function_exists('http_parse_headers')) {
    function http_parse_headers($raw_headers) {
        $headers = array();
        $key = '';

        foreach(explode("\n", $raw_headers) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[$h[0]]))
                    $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                }
                else {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                }

                $key = $h[0];
            }
            else {
                if (substr($h[0], 0, 1) == "\t")
                    $headers[$key] .= "\r\n\t".trim($h[0]);
                elseif (!$key)
                    $headers[0] = trim($h[0]);
            }
        }

        return $headers;
    }
}


class HTTPUtil
{
    // 2**20 as default maxRequestLength
    public static function initRequest($maxRequestLength = 1048576)
    {
        return new HTTPServerRequest($maxRequestLength);    
    }

    public static function getHeaderValue(string $key, array $arrHeaders, $func = null)
    {
        $mapping =  HTTP::HEADER_MAPPINGS; //HTTPMappings::HEADERS;
        
        if( !isset( $mapping[$key] ) )
            return null;

        list($default, $keyList) = $mapping[$key];

        foreach( $keyList as $k )
        {
            if( isset( $arrHeaders[$k] ) )
                return $arrHeaders[$k];
        }
        
        if( !is_null( $func ) && is_callable( $func ) )
            return $func;

        return $default;
    }

    public static function cookieToArray($str)
    {
        if( !is_string( $str ) )
        {
            Console::debug('No Cookie');
            return null;
        }

        $trimChars = ";\"  \t\n\r\0\x0B";
    
        for( $currPos = 0; strlen($str) > 0; )
        {
            $pos = strpos($str, "=");
            if( false === $pos ) break;
    
            $key = trim(substr($str, 0, $pos), $trimChars);
            $str = substr($str, $pos+1);
    
            $delim = ( $str[0] == '"' ) ? '";' : ';';
    
            $pos = strpos($str, $delim);
    
            if(false === $pos )
            {
                $r[$key] = trim($str, $trimChars);
                break;
            }
    
            $value = trim(substr($str, 0, $pos), $trimChars);
    
            $str = substr($str, $pos+1);
            $r[$key] = $value;
    
        }
    
        return $r;
    }

    private static function readFirstLine(HTTPServerRequest $http)
    {
        $pos = strpos($http->rawData, "\r\n");

        if( $pos === false )
            return false;

        $firstLine = substr($http->rawData, 0, $pos);

        if( 3 != count( $arrFirstLine = explode(" ", $firstLine ) ) )
        {
            $http->error = "Invalid Request $firstLine";
            return false;
        }

        list($method, $uri, $protocol) = $arrFirstLine;

        if( !in_array( $method, ['POST', 'GET', 'DELETE', 'HEAD', 'PUT', 'UPDATE'] ) )
        {
            $http->error = "Invalid method [$method]";
            return false;
        }

        $http->parseURL = parse_url($uri);

        if( isset( $http->parseURL['query'] ) )
            $http->queryString = $http->parseURL['query'];

        $http->requestURI = $uri;
        $http->scriptName = $http->parseURL['path'];

        $http->method = $method;

        list($http->protocol, $http->protocolVersion) = explode("/", $protocol);

        if( strlen($http->rawData) > ($pos + 2) )
            $http->rawData = substr($http->rawData, $pos + 2);
        else
            $http->rawData = "";        

        $http->setAttribute('firstLineRead', true);

        return true;
    }

    public static function readHeader($resource, HTTPServerRequest $http)
    {
        $data = StreamSocket::read($resource, 16384);

        if( is_null( $data ) )
        {
           $http->error = "Client Closed";
           return false;
        }

        $http->rawData = $http->rawData . $data;

        if( !$http->getAttribute('firstLineRead', false) &&
            false === self::readFirstLine($http) )
        {
                return false;
        }

        $pos = strpos($http->rawData, "\r\n\r\n");

        if( $pos === false )
        {
            return false;
        }

        $headerTxt = substr($http->rawData, 0, $pos);
        $body = substr($http->rawData, $pos + 4);

        $rawHeaders = http_parse_headers($headerTxt);

        $http->rawHeaders  = $rawHeaders;

        $http->remoteHost = StreamSocket::getRemoteHost($resource); 
        $http->contentLength = (int)self::getHeaderValue('CONTENT_LENGTH', $rawHeaders); 

        $cookie = self::getHeaderValue('COOKIE', $rawHeaders); 

        //$http->cookie = self::cookieToArray($cookie);
        $http->body = $body;

        $http->setAttribute('headerRead', true);

        return true;
    }    

    public static function readRequest($resource, HTTPServerRequest $http)
    {
         if( !$http->getAttribute('headerRead', false) )
         {
             $r = self::readHeader($resource, $http);

             if( $r === false )
             {
                 return $r;
             }

             if( 0 == $http->contentLength || strlen($http->body) == $http->contentLength )
                 return true;

             return false;
         }

         $data = StreamSocket::read($resource, 1);

         if( is_null( $data ) )
         {
             $http->error = 'connection closed';
             return false;
         }

         if( 0 == $http->contentLength )
         {
             $http->error = 'Invalid data received';
             return false;
         }

         $http->body .= $data;

         if( $http->contentLength == strlen($http->body) )
             return true;

         if( $http->contentLength < strlen($http->body) )
         {
             $http->error = "Invalid Request. Content Length too long " . strlen($http->body);
         }

         return false;
    }
}



