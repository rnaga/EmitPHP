<?php

// More WebSocket Examples

require __DIR__ . '/../vendor/autoload.php';

use Emit\WS\WSServer;

$app = (new WSServer())->listen(4000)->app();

$app->on('message', function($conn, $msg){

    // Send text message
    $conn->send("Hello World");

    // Send Fragmented messages
    $conn->sendFragBegin("Begin");
    $conn->sendFrag(" ====== ");
    $conn->sendFragEnd( "End" );

    // Send binary data
    $conn->sendBinary(pack('C', 0x01));

    // Send binary fragmented data
    $conn->sendFragBinaryBegin(pack('C', 0x01));
    $conn->sendFragBinary(pack('C', 0x02));
    $conn->sendFragBinaryEnd(pack('C', 0x03));

    // Broadcast message
    $conn->connsForEach(function($theOther, $conn) use ($msg){
        if( !$theOther->isMe($conn) ) {
            $theOther->send("Received msg from " . $conn->getResource() . " msg:$msg");
        }
    });
});

\Emit\Loop();


