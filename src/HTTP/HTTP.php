<?php

namespace Emit\HTTP;


class HTTP
{
    // http://php.net/manual/ja/function.http-response-code.php
    public static function responseCode(int $code) 
    {
        $text = 'Unknown';
        
        switch( $code ) 
        {
            case 100: $text = 'Continue'; break;
            case 101: $text = 'Switching Protocols'; break;
            case 200: $text = 'OK'; break;
            case 201: $text = 'Created'; break;
            case 202: $text = 'Accepted'; break;
            case 203: $text = 'Non-Authoritative Information'; break;
            case 204: $text = 'No Content'; break;
            case 205: $text = 'Reset Content'; break;
            case 206: $text = 'Partial Content'; break;
            case 300: $text = 'Multiple Choices'; break;
            case 301: $text = 'Moved Permanently'; break;
            case 302: $text = 'Moved Temporarily'; break;
            case 303: $text = 'See Other'; break;
            case 304: $text = 'Not Modified'; break;
            case 305: $text = 'Use Proxy'; break;
            case 400: $text = 'Bad Request'; break;
            case 401: $text = 'Unauthorized'; break;
            case 402: $text = 'Payment Required'; break;
            case 403: $text = 'Forbidden'; break;
            case 404: $text = 'Not Found'; break;
            case 405: $text = 'Method Not Allowed'; break;
            case 406: $text = 'Not Acceptable'; break;
            case 407: $text = 'Proxy Authentication Required'; break;
            case 408: $text = 'Request Time-out'; break;
            case 409: $text = 'Conflict'; break;
            case 410: $text = 'Gone'; break;
            case 411: $text = 'Length Required'; break;
            case 412: $text = 'Precondition Failed'; break;
            case 413: $text = 'Request Entity Too Large'; break;
            case 414: $text = 'Request-URI Too Large'; break;
            case 415: $text = 'Unsupported Media Type'; break;
            case 500: $text = 'Internal Server Error'; break;
            case 501: $text = 'Not Implemented'; break;
            case 502: $text = 'Bad Gateway'; break;
            case 503: $text = 'Service Unavailable'; break;
            case 504: $text = 'Gateway Time-out'; break;
            case 505: $text = 'HTTP Version not supported'; break;
        }

        return $text; 
    }

    const HEADER_MAPPINGS = [
        'REQUEST_METHOD' => [null, ['REQUEST_METHOD']],
        'CONTENT_LENGTH' => [null, ['CONTENT_LENGTH', 'Content-Length']],
        'COOKIE'         => [null, ['HTTP_COOKIE', 'Cookie']],
        'REMOTE_HOST'    => [null, ['REMOTE_ADDR', 'X-Forwarded-Server']],
        'USER_AGENT'     => [null, ['HTTP_USER_AGENT', 'User-Agent']],
        'QUERY_STRING'   => [null, ['QUERY_STRING']],
        'REQUEST_URI'    => [null, ['REQUEST_URI']],
        'HOST'           => [null, ['HTTP_HOST', 'HOST']],
        'ACCEPT'         => [null, ['HTTP_ACCEPT', 'Accept']],
        'ACCEPT_LANGUAGE' => ['null', ['HTTP_ACCEPT_LANGUAGE', 'Accept-Language']],
        'ACCEPT_ENCODING' => [null, ['HTTP_ACCEPT_ENCODING', 'Accept-Encoding']],
        'CONTENT_TYPE'    => ['plain/text', ['CONTENT_TYPE', 'Content-Type']],
        'SERVER_PROTOCOL' => ["HTTP/1.1", ['SERVER_PROTOCOL']],
        'COOKIE'          => [null, ['Cookie', 'HTTP_COOKIE']],
    ];
}


