<?php

namespace Emit\HTTP;

use Emit\ServerResponse;
use Emit\Server;
use Emit\NetConnection;
use Emit\StreamSocket;
use Emit\Event\ResourceEventEmitter;

class HTTPServerResponse extends ServerResponse
{
    protected $request;

    function __construct(HTTPServerRequest $request, NetConnection $netConn)
    {
        parent::__construct($netConn);
        $this->request = $request;
    }

    public function getStatusString()
    {
        $request    = $this->request;
        $statusCode = HTTP::responseCode($this->status); 

        $statusString = $request->protocol . "/" . $request->protocolVersion
               . " " . $this->status . " $statusCode\r\n";

        return $statusString;
    }

    public function _send( $data )
    {
        $resource = $this->getResource();

        $n = StreamSocket::write($resource,  $data, strlen( $data ) );
        return $n;
    }

    public function end( )
    {
        $resource = $this->getResource();
        $this->netConn->close();
    }
}

