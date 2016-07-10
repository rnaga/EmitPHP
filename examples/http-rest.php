<?php

// Something RESTFul

require __DIR__ . '/../vendor/autoload.php';

use Emit\HTTP\HTTPServer;
use Emit\StreamSocket;

$server = (new HTTPServer())->listen(4000);

// Create new Route
$route = $server->route();

// Mapping route to /resource/:id, where :id is [0-9]+
$server->use(['/resource/:id', ['id' => '[0-9]+']], $route);

// Create Resource
$route->post(function($req, $res){
    $res->send("Create resource for " . $req->params['id']);
    $res->end();
});

// Read Resource
$route->get(function($req, $res){
    $res->send("Read resource for " . $req->params['id']);
    $res->end();
});

// Update Resource
$route->put(function($req, $res){
    $res->send("Update resource for " . $req->params['id']);
    $res->end();
});

// Delete Resource
$route->delete(function($req, $res){
    $res->send("Delete resource for " . $req->params['id']);
    $res->end();
});

// 404 for all others
$server->on('request', function($req, $res){
    $res->status(404);
    $res->send('File not found');
    $res->end();
});

\Emit\Loop();




