<?php

// HTTP Server with Routing

require __DIR__ . '/../vendor/autoload.php';

use Emit\HTTP\HTTPServer;
use Emit\StreamSocket;

$server = (new HTTPServer())->listen(4000);

// Create new Route
$route = $server->route();

// Get method 
$route->get("/", function($req, $res, $next){
    $res->send("Hello World");
    // Calling the next handler
    $next();
});

// Post method
$route->post("/", function($req, $res ){
    $res->send("Hello World");
    $res->end();
});

// Matching /abcd
$route->get("/abcd", function($req, $res ){
    $res->send("/abcd");
    $res->end();
});

// 404 for all other requests
$route->all(function($req, $res){
    $res->status(404);
    $res->end();
});

// Register route
$server->use($route);

\Emit\Loop();

