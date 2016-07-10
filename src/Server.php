<?php

namespace Emit;

use Emit\Event\ResourceEventEmitter;
use Emit\StreamSocket;
use Emit\Config;

class Server extends ResourceEventEmitter
{
    private $config;

    function __construct()
    {
        parent::__construct();
        return $this;
    }

    public function readConfig(array $arr)
    {
        $config = Config::read($arr);
        return $this;
    }

    public function getConfig($key)
    {
        return $this->config->get($key);
    }

    final public function listen(...$args)
    {
        if( is_string( $args[0] ) )
        {
            $host = $args[0];

            if( !isset( $args[1] ) || !is_int( $args[1] ) )
                return;

            $port = $args[1];
        }
        else
        {
            if( !is_int( $args[0] ) )
                return;

            $host = '0.0.0.0';
            $port = $args[0];
        }

        Console::debug("Server listening on host: $host port: $port");

        $resource = StreamSocket::createServerSocket( $host, $port );

        $this->on("read", function($server, $resource) {

            $remoteResource = StreamSocket::accept($resource);
            Console::debug("Accept new client rource: $remoteResource");
            $server->emit("accept", $server, $remoteResource);
    
        })->listenResource($resource);

        return $this;
    } 
}

