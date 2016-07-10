<?php

// HTTP Server with Routing

require __DIR__ . '/../vendor/autoload.php';

use Emit\HTTP\HTTPServer;
use Emit\StreamSocket;
use Emit\Router\PathMatcher;

$server = (new HTTPServer())->listen(4000);

// Create new Route
$route = $server->route();

// Path with regex. It matches as /abc/1234/def/ 
$regex = PathMatcher::regex('([a-z]+)/([0-9]+)/(.+)');

$route->get($regex, function($req, $res, $next){

    // Get parameters
    $params = $req->params;

    $res->send("Regex => " . print_r($params, 1));
    $res->end();
});

// Set parameters for 'id' and 'name' with regex
$route->get(['/:id/:name', ['id' => '[0-9]+', 'name' => '[a-z]+']], function($req, $res, $next ){

    // Get parmameters
    $params = $req->params; 

    $res->send("Parameters => " . print_r( $params, 1));
    $res->end();
});

// Easier way to set a parameter
$route->get('/:any', function($req, $res, $next ){
    // Get parmameters
    $params = $req->params;

    $res->send("Parameters => " . print_r( $params, 1));
    $res->end();
});

// How to retrieve request in body
$route->post(function($req, $res, $next){

    $body = $req->body;

    // Parse request -- or use json_decode for json format
    parse_str($body, $query);

    $res->send("Body => " . print_r( $query, 1));
    $res->end();
});

// 404 for all other requests
$route->all(function($req, $res){
    $res->status(404);
    $res->send('');
    $res->end();
});

// Register the route
$server->use($route);

\Emit\Loop();

