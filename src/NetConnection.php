<?php

namespace Emit;

use Emit\Event\ResourceEventEmitter;
use Emit\Server;
use Emit\StreamSocket;
use Emit\Attribute;

class NetConnection extends Attribute
{
    protected $remote;
    protected $server;

    function __construct(ResourceEventEmitter $remote, Server $server)
    {
        $this->remote = $remote;
        $this->server = $server;
    }

    public function getRemote()
    {
        return $this->remote;
    }

    public function getResource()
    {
        return $this->remote->getResource();
    }

    public function write($data, $blocking = 0)
    {
        $resource = $this->getResource();
        $r = StreamSocket::write( $resource, $data, strlen($data), $blocking);
        return $r;
    }

    public function read($length = 8192, $blocking = 0)
    {
        $resource = $this->getResource();
        $data = StreamSocket::read( $this->resource, $length, $blocking );
        return $data;
    }

    public function close()
    {
        $this->server->emit("netconnection_closed", $this);
        $this->remote->close();
    }
}





