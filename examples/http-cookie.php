<?php

// Simple HTTP Server

require __DIR__ . '/../vendor/autoload.php';

use Emit\HTTP\HTTPServer;

$server = (new HTTPServer())->listen(4000);

$route = $server->route();

// Parse cookie when requested
$route->get([$server, 'cookieParser']);

$route->get(function($req, $res){

   // Get Cookies
   $cookie = $req->cookie;

   // Set Cookie1
   $res->cookie('key1', 1, ['path' => '/']);
   $res->cookie('key2', 2, ['path' => '/']);

   $res->send('Cookie => ' . print_r( $cookie, 1 ) );
   $res->end();   

});

$server->use($route);

\Emit\Loop();

