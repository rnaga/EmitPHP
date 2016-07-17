<?php

namespace EmitTest;

use PHPUnit\Framework\TestCase;
use Emit\WS\WSUtil;
use Emit\WS\WS;

class WSFrameTest extends TestCase
{
    public function testFrame()
    {
        $data = str_repeat("1", 10);

        $frame = WSUtil::initFrame();

        $pack = WSUtil::packFrame($frame, $data);

        $this->assertEquals(null, $frame->error);

        $frame2 = WSUtil::initFrame();

        $unpack = WSUtil::unpackFrame($frame2, $pack);

        $this->assertEquals(true, $unpack);
        $this->assertEquals(strlen($data), $frame2->payloadLen);
        $this->assertEquals($data, $frame2->payload);
    }

    public function testFrameClose()
    {
        $data = "closing the connection";

        $frame = WSUtil::initFrame()
               ->option("opcode", WS::FRAME_OPCODE_CLOSE)
               ->option("closeStatus", 1002);

        $pack = WSUtil::packFrame($frame, $data);

        $this->assertEquals(null, $frame->error);

        $frame2 = WSUtil::initFrame();

        $unpack = WSUtil::unpackFrame($frame2, $pack);

        $this->assertEquals(true, $unpack);
        $this->assertEquals(1002, $frame2->closeStatus);
        $this->assertEquals(true, $frame2->isClosing);

    }

}

