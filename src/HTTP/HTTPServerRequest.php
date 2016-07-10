<?php

namespace Emit\HTTP;

use Emit\Attribute;

class HTTPServerRequest extends Attribute
{
    public $method ;
    public $parseURL;
    public $requestURI;
    public $scriptName;
    public $queryString;
    public $protocol;
    public $protocolVersion;
    public $remoteHost;
    public $contentLength;
    public $body;
    public $rawHeaders;
    public $rawData;
    public $cookie;
    public $maxRequestLength;
    public $params;
    public $error;

    function __construct($maxRequestLength = 1048576)
    {
        $this->contentLength = 0;
        $this->env = array();
        $this->maxRequestLength = $maxRequestLength;
        $this->error = null;
    }
}

