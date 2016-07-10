<?php

namespace Emit\FCGI;

use Emit\ServerResponse;

class FCGIServerResponse extends ServerResponse
{
    public $fcgi;

    function __construct($fcgi, $netConn)
    {
        parent::__construct($netConn);
        $this->fcgi = $fcgi;
    }

    public function getStatusString()
    {
        return "Status: " . $this->status . "\r\n";
    }

    public function _send( $data )
    {
        $resource = $this->getResource();

        $n = FCGIUtil::write($resource,  $this->fcgi, FCGIRequestType::FCGI_STDOUT, $data, strlen( $data ) );
        $n = FCGIUtil::flush( $resource, $this->fcgi, 0 );
        return $n;
    }

    public function end( )
    {
        $resource = $this->getResource();

        $r = FCGIUtil::finishRequest( $resource, $this->fcgi, 1 );
        $this->netConn->close();
    }
}


