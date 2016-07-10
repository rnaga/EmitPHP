<?php

namespace Emit;

use Emit\Attribute;
use Emit\NetConnection;

abstract class ServerResponse extends Attribute
{
    protected $status;
    public $netConn;
    protected $headers;
    protected $cookies;

    function __construct($netConn)
    {
        $this->status = 200;
        $this->netConn = $netConn;
        $this->headerSent = false;
        $this->cookies = array();

        $this->headers = array
        (
            'Content-Type' => 'text/plain',
        );
    }

    abstract public function end();
    abstract protected function _send($data);
    abstract protected function getStatusString();

    public function getResource()
    {
        return $this->netConn->getResource();
    }

    public function status($status)
    {
        $this->status = $status;
    }

    public function set($keyValues)
    {
        foreach( $keyValues as $k => $v )
        {
            $this->append($k, $v);
        }
    }

    public function append($key, $value)
    {
        $this->headers[$key] = $value; 
        return $this;
    }

    public function setHeader($key, $value)
    {
        return $this->append($key, $value);
    }

    public function unsetHeader($key)
    {
        unset( $this->headers[$key] );
    }

    public function cookie($key, $value = "", $options = null)
    {
        $index = count($this->cookies);

        $this->cookies[$index] = array
        (
            'name'  => $key,
            'value' => $value,
        );

        foreach( $options as $k => $v )
        {
            if( preg_match( '/^(expires|domain|path|secure|httpOnly|maxAge|signed)$/', $k ) )
            {
                $this->cookies[$index]['options'][$k] = $v;
            }
        }

        return $this;
    }

    public function getHeadersString()
    {
        $headerTxt = $this->getStatusString();

        foreach( $this->headers as $k => $v )
        {
            $headerTxt .= "$k: $v\r\n";
        }

        foreach( $this->cookies as $cookie )
        {
            $cookieTxt = "Set-Cookie: "
                       . $cookie['name'] . "=" . $cookie['value'] ."; ";

            if( count($cookie['options']) )
            {
                foreach( $cookie['options'] as $k => $v )
                {
                    $cookieTxt .= "$k=$v; ";
                }
            }

            $headerTxt .= "$cookieTxt\r\n";
        }

        $headerTxt .= "\r\n";

        return $headerTxt;
    }

    public function send($data)
    {
        $headerTxt = "";

        $headerSent = $this->getAttribute("headerSent", false);

        if( !$headerSent )
        {
            $headerTxt = $this->getHeadersString();
            $this->setAttribute("headerSent", true);
        }

        $this->_send($headerTxt.$data);
    }

}

