<?php

namespace Emit\Event;

use Emit\StreamSocket;
use Emit\Console;

class ResourceEventEmitter extends EventEmitter
{
    protected $resource;
    protected $isClosed;

    function __construct()
    {
        parent::__construct();
    }

    final public function listenResource($resource)
    {
        if( !is_resource( $resource ) )
        {
            Console::warn("Is not resource");
            return;
        }

        if( !$this->has("read") )
        {
            Console::warn("Must set 'read' event");
            return;
        }

        $this->resource = $resource;
        GlobalEventEmitter::addNewResource($this);
    }

    public function getResource()
    {
        return $this->resource;
    }

    final public function close()
    {
        GlobalEventEmitter::destroy($this);
        StreamSocket::close($this->resource);
        $this->isClosed = true;
    }

    final public function isClosed()
    {
        return $this->isClosed;
    }
}



