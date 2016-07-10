<?php

namespace Emit\WS;

use Emit\NetConnection;
use Emit\Event\ResourceEventEmitter;
use Emit\Console;

class WSNetConnection extends NetConnection
{
    public $id;
    public $frameIn;
    public $frameOut;
    public $app;

    function __construct(ResourceEventEmitter $remote, WSServer $server, WSApplication $app) 
    {
        parent::__construct($remote, $server);

        $this->frameIn  = WSUtil::initFrame();
        $this->frameOut = WSUtil::initFrame();

        $this->app = $app;
    }

    public function isMe(WSNetConnection $netConn)
    {
        return $netConn->id == $this->id;
    }

    public function connsForEach($func, ...$args)
    {
        if( !is_callable($func) ) return;

        $netConns = $this->app->netConns;

        foreach( $netConns as $id => $netConn )
        {
            $func($netConn, $this, ...$args);
        }
    }

    public function broadCast($data)
    {
        $app = $this->app;
        $netConns = $app->netConns;

        foreach( $netConns as $id => $netConn )
        {
            if( $this->equals($netConn) )
                continue;

            $app->emit("broadcast", $netConn, $data);
        }
    }

    public function send($data, $isBinary = false)
    {
        $frameOut = $this->frameOut;
        $frameOut->reset();

        $opcode = ($isBinary ) ? WS::FRAME_OPCODE_BINARY : WS::FRAME_OPCODE_TEXT;
        
        return $this->write($data, array('opcode' => $opcode));
    }

    public function sendBinary($data)
    {
        return $this->send($data, true);
    }

    public function sendClose($closeStatus = WS::FRAME_CLOSE_NORMAL, $data = "closing")
    {
        $frameOut = $this->frameOut;
        $frameOut->reset();

        $frameOut->opcode = WS::FRAME_OPCODE_CLOSE;
        $frameOut->closeStatus = $closeStatus;

        $params = array
        (
            'noReset'     => true,
            'opcode'      => WS::FRAME_OPCODE_CLOSE,
            'closeStatus' => $closeStatus,
        );

        $this->write($data, $params);
    }

    // https://tools.ietf.org/html/rfc6455#section-5.4
    //
    // EXAMPLE: For a text message sent as three fragments, the first
    //  fragment would have an opcode of 0x1 and a FIN bit clear, the
    //  second fragment would have an opcode of 0x0 and a FIN bit clear,
    //  and the third fragment would have an opcode of 0x0 and a FIN bit
    //  that is set.
    //
    // https://tools.ietf.org/html/rfc6455#section-5.7
    //
    // A fragmented unmasked text message
    //  *  0x01 0x03 0x48 0x65 0x6c (contains "Hel")
    //  *  0x80 0x02 0x6c 0x6f (contains "lo")
    public function sendFragBegin($data, $isBinary = false)
    {
        $frameOut = $this->frameOut;
        $frameOut->reset();

        $fin = 0;
        $opcode = ($isBinary ) ? WS::FRAME_OPCODE_BINARY : WS::FRAME_OPCODE_TEXT;

        $params = array
        (
            'noReset' => true,
            'fin'     => 0,
            'opcode'  => $opcode,
        );

        return $this->write($data, $params);
    }

    public function sendFragBinaryBegin($data, $isBinary = false)
    {
        return $this->sendFragBegin($data, true);
    }

    public function sendFrag($data, $isEnd = false, $isBinary = false)
    {
        $frameOut = $this->frameOut;

        $params = array
        (
            'noReset' => true,
            'fin'     => ($isEnd) ? 1 : 0,
            'opcode'  => WS::FRAME_OPCODE_CONT,
        );

        return $this->write($data, $params);        
    }

    public function sendFragBinary($data, $isEnd = false)
    {
        return $this->sendFrag($data, $isEnd, true);
    }

    public function sendFragEnd($data, $isBinary = false)
    {
        return $this->sendFrag($data, true, $isBinary);
    }

    public function sendFragBinaryEnd($data)
    {
        return $this->sendFrag($data, true, true);
    }

    public function sendPing()
    {
        return self::write('', ['ping' => true]);
    }

    public function sendPong()
    {
        return self::write('', ['pong' => true]);
    }

    // @Override
    public function write($data, $params = null)
    {
        $frameOut = $this->frameOut;

        if( !isset($params['noReset']) )
            $frameOut->reset();

        $options = ['fin', 'opcode', 'closeStatus'];

        foreach( $options as $option )
        {
            if( isset($params[$option]) )
                $frameOut->$option = $params[$option];
        }

        Console::debug("Sending data opcode: " . $frameOut->opcode . " payloadLen: " . $frameOut->payloadLen);

        if( isset( $params['pong'] ) )
            $frameData = WSUtil::packPong($frameOut);
        else if( isset( $params['ping'] ) )
            $frameData = WSUtil::packPing($frameOut);
        else
            $frameData = WSUtil::packFrame($frameOut, $data); 

        return parent::write($frameData); 
    }

    // @Override
    public function read($length = 16384, $blocking = 0)
    {
        $resource = $this->getResouce();
        $frameIn  = $this->frameIn;

        return WSUtil::readFrame($resource, $frameIn);
    }

    // @Override
    public function close($closeStatus = WS::FRAME_CLOSE_NORMAL)
    {
        $frameIn = $this->frameIn;
        
        if( !$frameIn->isClosing )
        {
            $this->sendClose($closeStatus, "close");
        }

        parent::close();
    }
}

 
