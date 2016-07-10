<?php

namespace Emit\WS;

use Emit\ServerResponse;
use Emit\Server;
use Emit\HTTP\HTTP;
use Emit\NetConnection;
use Emit\StreamSocket;
use Emit\Event\ResourceEventEmitter;

class WSServerResponse extends ServerResponse
{
    protected $request;

    function __construct(WSServerRequest $request, NetConnection $netConn)
    {
        parent::__construct($netConn);
        $this->request = $request;
        $this->status  = 101;

        // Add Headers as below
        //
        // Upgrade: websocket
        // Connection: Upgrade
        // Sec-WebSocket-Accept: $accept
        $this->append('Upgrade', 'websocket');
        $this->append('Connection', 'Upgrade');
     
        $key = $request->secWebSocket['Key'];
        $accept = base64_encode(sha1($key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));

        $this->append('Sec-WebSocket-Accept', $accept); 
    }

    public function getStatusString()
    {
        $request    = $this->request;
        $statusString = $request->protocol . "/" . $request->protocolVersion . " ";

        if( $this->status == 101 )
        {
            $statusString .= "101 Switching Protocols\r\n";
        }
        else
        {
            $statusCode = HTTP::responseCode($this->status);
            $statusString .= $this->status . " $statusCode\r\n";
        }

        return $statusString;
    }

    public function sendHeaders()
    {
        $this->send();
    }

    // @Override
    public function send($data = null)
    {
        $headerSent = $this->getAttribute("headerSent", false);

        if( $headerSent ) return 0;

        $resource = $this->getResource();

        // Send headers only
        $data = $this->getHeadersString();

        $n = StreamSocket::write($resource,  $data, strlen( $data ) );
       
        $this->setAttribute("headerSent", true);

        return $n;
    } 

    public function _send( $data )
    {
        // Do nothing
        return;
    }

    public function end( )
    {
        $resource = $this->getResource();
        $this->netConn->close();
    }
}

