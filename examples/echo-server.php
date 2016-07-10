<?php

// Simple Echo server

require __DIR__ . '/../vendor/autoload.php';

use Emit\Server;
use Emit\Event\ResourceEventEmitter;
use Emit\StreamSocket;

$server = (new Server())->listen(4000);

$server->on('accept', function($sever, $resource){

    $remote = new ResourceEventEmitter();

    $remote->on("read", function($remote, $resource) {

        // Receives data
        $data = StreamSocket::read($resource);

        // Close the connection if disconnected by the client
        if( is_null( $data ) )
        {
            $remote->close();
            return;
        }

        // Echoes data to the client
        StreamSocket::write($resource, "echo => " . $data);

    })->listenResource($resource);
});

\Emit\Loop();
