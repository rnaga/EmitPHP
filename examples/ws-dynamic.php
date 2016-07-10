<?php

// Create WSApp dynamically

require __DIR__ . '/../vendor/autoload.php';

use Emit\WS\WSServer;

$ws = (new WSServer())->listen(4000);

$ws->on('before_connect', function($ws, $req){

    $appName = 'dynamic';
    $req->appName = $appName;

    if( !is_null( $ws->getApp($appName) ) )
        return;

    $app = $ws->app($appName);

    $app->on('message', function($conn, $msg){

        // Echoes message
        $conn->send("echo => ". $msg);

        // Close the connection
        $conn->close();
    }); 
});

\Emit\Loop();


