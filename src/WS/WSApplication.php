<?php

namespace Emit\WS;

use Emit\StreamSocket;
use Emit\Event\ResourceEventEmitter;
use Emit\Event\EventEmitter;
use Emit\Console;

class WSApplication extends EventEmitter
{
    public $appName;
    public $netConns;

    public function __construct($appName)
    {
        parent::__construct();
        $this->appName = $appName;
    }

    public function addNetConn(WSNetConnection $netConn)
    {
        $id = (int)$netConn->getResource();
        $this->netConns[$id] = $netConn;
        $netConn->id = $id;

        Console::debug("Adding new netConn to WSApplication: $id");
    }

    public function removeNetConn(WSNetConnection $netConn)
    {
        $id = (int)$netConn->getResource();
        unset( $this->netConns[$id] );

        Console::debug("Removing netConn from WSApplication: $id");
    }

    public function addClient(ResourceEventEmitter $remote, WSNetConnection $netConn)
    {
        $remote->setAttribute('app', $this); 
        $remote->setAttribute('appName', $this->appName);
        $remote->setAttribute("netConn", $netConn);

        $this->addNetConn($netConn);

        $self = $this;

        $remote->on('resource_closed', function($rId) use ($self, $netConn){
            $self->removeNetConn($netConn);
        });

        // Remove 'read' event which was set by the server
        $remote->unset('read');

        // Re-set 'read' event
        $remote->on('read', function($remote, $resource) use ($self){

            $netConn  = $remote->getAttribute('netConn');
    
            $frameIn  = $netConn->frameIn;
            $frameOut = $netConn->frameOut;
    
            $resource = $remote->getResource();
    
            $r = WSUtil::readFrame($resource, $frameIn);
    
            if( ( !$r && !is_null( $frameIn->error ) ) ||  $frameIn->opcode == WS::FRAME_OPCODE_CLOSE )
            {
                $this->emit("close", $netConn, $frameIn);
                $remote->close();

                Console::debug("Connection closed. Removing netConn from WSApplication: $netConn->id error:[" . $frameIn->error . "]");

                return;
            }

            if( !$r )
            {
                // Didin't get all frames
                return;
            }
   
            if( $frameIn->opcode == WS::FRAME_OPCODE_PONG )
            {
                $this->emit("pong");
                Console::debug("pong");
                return;
            }

            if( $frameIn->opcode == WS::FRAME_OPCODE_PING )
            {
                $this->emit("ping");
                Console::debug("ping");
                $netConn->sendPong();
                return;
            }

            Console::debug("Frame received opcode: " . $frameIn->opcode . " payloadLen: " . $frameIn->payloadLen);
 
            $this->emit("message", $netConn, $frameIn->payload);
            $frameIn->reset();
        });

        $this->emit("init", $netConn);
    }
}



