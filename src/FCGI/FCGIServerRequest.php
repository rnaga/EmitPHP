<?php

namespace Emit\FCGI;

use Emit\HTTP\HTTPServerRequest;
use Emit\HTTP\HTTPUtil;

class FCGIServerRequest extends HTTPServerRequest
{
/*
    public $method ;
    public $parseURL;
    public $requestURI;
    public $queryString;
    public $protocol;
    public $protocolVersion;

    public $remoteHost;
    public $contentLength;

    public $body;
    public $rawHeaders;
    public $rawData;

    public $maxRequestLength;
    public $attr;

    public $error;
    public $headerRead = false;
*/
    public $fcgi;

    function __construct($fcgi, $body)
    {
        parent::__construct();

        $this->fcgi = $fcgi;
        $this->rawData = $body;
        $this->body    = $body;

        $env = $fcgi->env;

        $this->method = HTTPUtil::getHeaderValue("REQUEST_METHOD", $env);
        $this->queryString = HTTPUtil::getHeaderValue("QUERY_STRING", $env); 

        $protocol = HTTPUtil::getHeaderValue("SERVER_PROTOCOL", $env);
        list($this->protocol, $this->protocolVersion) = explode("/", $protocol);
        
        $this->method = HTTPUtil::getHeaderValue("REQUEST_METHOD", $env);
        $this->remoteHost  = HTTPUtil::getHeaderValue("REMOTE_HOST", $env);
        $this->contentLength = HTTPUtil::getHeaderValue("CONTENT_LENGTH", $env);
        $this->cookie = HTTPUtil::getHeaderValue("COOKIE", $env);

        $this->requestURI = $env['REQUEST_URI'];
        $this->scriptName = $env['SCRIPT_NAME'];

        $this->rawHeaders = $env;
    }
}





