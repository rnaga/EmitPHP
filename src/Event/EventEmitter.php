<?php

namespace Emit\Event;

use Emit\Attribute;
use Emit\Console;

class EventEmitter extends Attribute
{
    protected $events = null;
    public $id;

    function __construct()
    {
        $this->events = [];
        $this->id = GlobalEventEmitter::register($this);

        return $this;
    }
 
    function __destruct()
    {
        GlobalEventEmitter::notifyDestruct($this);
    }

    final public function has($eventName)
    {
        return isset( $this->events[$eventName] );
    }

    final public function unset($eventName)
    {
        unset( $this->events[$eventName] );
    }

    final public function on($eventName, $func)
    {
        $this->events[$eventName] = ['function' => $func];
        return $this;
    } 

    final public function emit($eventName, ...$args)
    {
        if( !$this->has($eventName) )
        {
            Console::debug("EventName undefined $eventName");
            return;
        }

        $emit = $this->events[$eventName];
        return $emit['function'](...$args);
    }
}




