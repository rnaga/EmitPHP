<?php

namespace EmitTest;

use PHPUnit\Framework\TestCase;
use Emit\Event\ResourceEventEmitter;
use Emit\StreamSocket;

class ResourceEventEmitterTest extends TestCase
{
    protected $e;

    protected function setUp()
    {
        $this->e = new ResourceEventEmitter();
    }

    public function testInstance()
    {
        $this->assertInstanceOf(ResourceEventEmitter::class, $this->e);
    }

    /**
        @depends testInstance
    */
    public function testListenResource()
    {
        $e = $this->e;

        // Get warned if not resource
        $e->listenResource(null);

        $this->assertEquals(null, $e->getResource()); 

        $resource = StreamSocket::createServerSocket('0.0.0.0', 4000);

        $e->listenResource($resource);

        // Need to define 'read' event
        $this->assertEquals(null, $e->getResource());

        $e->on('read', function($e, $resource){});

        $e->listenResource($resource);

        $this->assertEquals($resource, $e->getResource());
    }
}




