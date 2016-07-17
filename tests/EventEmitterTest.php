<?php

namespace EmitTest;

use PHPUnit\Framework\TestCase;
use Emit\Event\EventEmitter;

class EventEmitterTest extends TestCase
{
    protected $e;

    protected function setUp()
    {
        $this->e = new EventEmitter();
    }

    public function testInstance()
    {
        $this->assertInstanceOf(EventEmitter::class, $this->e);
    }

    /**
        @depends testInstance
    */
    public function testOn()
    {
        $e = $this->e;

        $e->on('new_event', function($params){});

        $this->assertEquals(true, $e->has('new_event')); 
    }

    /**
        @depends testInstance
    */
    public function testUnSet()
    {
        $e = $this->e;

        $e->on('new_event', function($params){});

        $this->assertEquals(true, $e->has('new_event'));

        $e->unset('new_event');

        $this->assertEquals(false, $e->has('new_event'));
    }

    /**
        @depends testInstance
    */
    public function testEmit()
    {
        $e = $this->e;
       
        $i = 1;

        $e->on('event', function() use (&$i){
            $i = 2;        
        });

        // $i is updated after this
        $e->emit('event');

        $this->assertEquals(2, $i);

    }
}




