<?php

namespace Emit\FCGI;

final class FCGI
{
    public $id; //int
    public $keep; //int
    public $closed; //int

    public $inLen; //int
    public $inPad; //int

    public $outHdr; //FCGIHeader
    public $outBuf; //unsinged char [1024*8];
    public $reserved; //unsigned char [sizeof(fcgi_end_request_rec)];

    public $env; //hash table

    function __construct()
    {
        $this->id = -1;
        $this->inLen = 0;
        $this->inPad = 0;
        $this->outHdr = NULL;
    }
}


