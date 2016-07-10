<?php

// WebSocket and intervalTimeout

require __DIR__ . '/../vendor/autoload.php';

use Emit\WS\WSServer;
use Emit\Timeout;

$app = (new WSServer())->listen(4000)->app();

$i = 0;

$app->on('init', function($conn) use (&$i){

    // Send Loop => $i for 10 times then close the connection
    Timeout::interval(function() use ($conn, &$i){
        if( $i > 10 )
        {
            $conn->send('bye');
            $conn->close();
            return false;
        }

        $conn->send("Loop => ". ($i++));
        return true;
    }, 1000);
});

\Emit\Loop();


