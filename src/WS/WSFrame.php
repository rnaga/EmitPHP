<?php

namespace Emit\WS;

class WSFrame
{
    public $fin;
    public $opcode;
    public $mask;
    public $maskingKey;
    public $payloadLen;
    public $payload;
    public $buf; 
    public $bufPayload;
    public $closeStatus;
    public $closeStatusString;
    public $isClosing;
    public $error;

    public function reset()
    {
        $this->error = 
        $this->payload =
        $this->payloadLen =
        $this->closeStatus =
        $this->maskingKey = null;

        $this->opcode = 1;
        $this->fin = 1;

        $this->mask = 0x00; 

        $this->buf =
        $this->bufPayload = "";

        $this->isClosing = false;

        return $this;
    }

    public function setOptions(array $options)
    {
        foreach( $options as $key => $value )
        {
            if( !is_string( $key ) )
                continue;
            $this->setOption($key, $value);
        }

        return $this;
    }

    public function setOption(string $key, $value)
    {
        if( !property_exists($this, $key) )
            return $this;

        $this->$key = $value;
        return $this;
    }

    public function options(array $options)
    {
        return $this->setOptions($options);
    }

    public function option(string $key, $value)
    {
        return $this->setOption($key, $value);
    }

    function __construct()
    {
        $this->reset();
        return $this;
    }
}
