<?php

// Timeout that works similar to setTimeout in JavaScript

require __DIR__ . '/../vendor/autoload.php';

use Emit\Timeout;

// setTimeout
Timeout::set(function(){
    echo "Display this after 1 second\n";
}, 1000);


$i = 0;

// setInterval
Timeout::interval(function() use (&$i){
    if($i > 10)
        // Out of the loop
        return false;

    echo "Loop " . ($i++) . " times\n";
    return true;

}, 1000);

\Emit\Loop();

