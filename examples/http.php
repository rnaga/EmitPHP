<?php

// Simple HTTP Server

require __DIR__ . '/../vendor/autoload.php';

use Emit\HTTP\HTTPServer;

$server = (new HTTPServer())->listen(4000);

$server->on('request', function($req, $res){

    // Send response
    $res->send("Hello World");

    // Close connection
    $res->end();

});

\Emit\Loop();

