<?php

// WebSocket simple text chat application

require __DIR__ . '/../../vendor/autoload.php';

use Emit\WS\WSServer;
use Emit\HTTP\HTTPServer;

// Create HTTP Server to deliver a HTML file
$http = (new HTTPServer())->listen(4001);

$http->on('request', function($req, $res){

    // Set Content-Type
    $res->setHeader('Content-Type', 'text/html');

    // Get html file
    $html = file_get_contents(__DIR__ . '/html/index.html');

    // Send it to the client
    $res->send($html);
    $res->end();
});

// Create WS Server
$ws = (new WSServer())->listen(4000);

// Triggers after WS handshake is done
$ws->on('connect', function($req, $res){

    $conn = $res->netConn;

    // Assign random ID and store it into the attribute
    $conn->attr('user_id', "user_" . rand());
});

// Create the new app
$app = $ws->app('ws-chat');

// Triggers once when connection is estashlished
$app->on('init', function($conn){

    // Get the user id
    $userId = $conn->attr('user_id');

    // Tell others someone joins the chat room
    $conn->connsForEach(function($theOther, $conn) use ($userId){
        if( !$theOther->isMe($conn) ) {
            $theOther->send("$userId joins the chat room");
        }
    });
});

// Triggers when message is received
$app->on('message', function($conn, $msg){

    // Get the user id
    $userId = $conn->attr('user_id');

    // Broadcast message
    $conn->connsForEach(function($theOther, $conn) use ($msg, $userId){
        if( !$theOther->isMe($conn) ) {
            $theOther->send("From $userId: $msg");
        }
    });
});


// Triggers when connection is closed
$app->on('close', function($conn){

    // Get the user id
    $userId = $conn->attr('user_id');

    // Tell others someone left the chat room
    $conn->connsForEach(function($theOther, $conn) use ($userId){
        if( !$theOther->isMe($conn) ) {
            $theOther->send("$userId left the chat room");
        }
    });
});

\Emit\Loop();





