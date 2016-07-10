<?php

// Simple FCGI Responder

// How to setup fcgi with apache
// https://httpd.apache.org/docs/trunk/mod/mod_proxy_fcgi.html
// 
// Single application instance
// ProxyPass "/myapp/" "fcgi://localhost:9000/"

require __DIR__ . '/../vendor/autoload.php';

use Emit\FCGI\FCGIServer;

$server = (new FCGIServer())->listen(9000);

$server->on('request', function($req, $res){

    // Send response
    $res->send("Hello World");

    // Close connection
    $res->end();

});

\Emit\Loop();

