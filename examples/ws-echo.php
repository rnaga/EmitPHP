<?php

// WebSocket Echo Server

require __DIR__ . '/../vendor/autoload.php';

use Emit\WS\WSServer;

$app = (new WSServer())->listen(4000)->app();

$app->on('message', function($conn, $msg){

    // Echoes message
    $conn->send("echo => ". $msg);

    // Close the connection
    $conn->close();
});

\Emit\Loop();


