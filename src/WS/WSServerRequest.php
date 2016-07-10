<?php

namespace Emit\WS;

use Emit\HTTP\HTTPServerRequest;

class WSServerRequest extends HTTPServerRequest
{
/*
    public $method ;
    public $parseURL;
    public $requestURI;
    public $queryString;
    public $protocol;
    public $protocolVersion;

    public $remoteHost;
    public $contentLength;

    public $body;
    public $rawHeaders;
    public $rawData;

    public $maxRequestLength;

    public $error;
*/ 
    const SEC_WEBSOCKET_KEYS = array('Key', 'Protocol', 'Version', 'Accept', 'Extensions');

    public $secWebSocket;

    public $origin;
    public $upgrade;
    public $connection;

    public $appName;

    function __construct($maxRequestLength = 1048576)
    {
        parent::__construct($maxRequestLength);
        $this->contentLength = 0;
        $this->env = array();
        $this->maxRequestLength = $maxRequestLength;
        $this->error = null;

        $this->secWebSocket = [];

        foreach( self::SEC_WEBSOCKET_KEYS as $key )
        {
            $this->secWebSocket[$key] = "";
        }
    }

    public function setWSHeaders()
    {
        foreach( $this->rawHeaders as $key => $value )
        {
            // Accepts below
            //    Sec-WebSocket-Key
            //    Sec-WebSocket-Protocol
            //    Sec-WebSocket-Version
            //    Sec-WebSocket-Accept
            //    Sec-WebSocket-Extensions
            //
            // 14 => strlen("Sec-WebSocket-")
            if( strlen($key) > 14 && substr($key, 0, 14) == "Sec-WebSocket-" )
            {
                $seckey = substr($key, 14);

                if( in_array($seckey, self::SEC_WEBSOCKET_KEYS) )
                {
                    $this->secWebSocket[$seckey] = $value; 
                }  
            }
            else if( in_array($key, ['Origin', 'Connection', 'Upgrade']) )
            {
                $key = strtolower($key);
                $this->$key = $value;
            }
        }
    }
}







