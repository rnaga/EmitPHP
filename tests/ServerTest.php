<?php

namespace EmitTest;

use PHPUnit\Framework\TestCase;
use Emit\Server;

class ServerTest extends TestCase
{
    public function testValidArguments1()
    {
        $server1 = (new Server())->listen(4000);
        $server2 = (new Server())->listen("0.0.0.0", 4001);
    }

    /**
        @expectedException \Emit\EmitException
    */
    public function testInvalidArguments1()
    {
        $server = (new Server())->listen(1,1);
    }

    /**
        @expectedException \Emit\EmitException
    */
    public function testInvalidArguments2()
    {
        $server = (new Server())->listen();
    }

    /**
        @expectedException \Emit\EmitException
    */
    public function testInvalidArguments3()
    {
        $server = (new Server())->listen("");
    }
}
