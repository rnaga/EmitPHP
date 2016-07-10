<?php

namespace Emit\FCGI;

use Emit\Event\ResourceEventEmitter;
use Emit\StreamSocket;
use Emit\NetConnection;
use Emit\Attribute;
use Emit\HTTP\HTTPServer;
use Emit\Console;

class FCGIServer extends HTTPServer
{
    function __construct()
    {
        parent::__construct();
    }

    // @Override
    public function onAccept($server, $resource)
    {
        $remote = new ResourceEventEmitter();
        $remote->setAttribute("server", $server);

        $remote->on("read", function($remote, $resource) use ($server){

            $fcgi = $remote->getAttribute('fcgi');

            if( is_null( $fcgi ) )
            {
                $fcgi = FCGIUtil::initRequest( );
                $remote->setAttribute('fcgi', $fcgi);

                list( $r, $error ) = FCGIUtil::readRequest( $resource, $fcgi );

                if( !$r )
                {
                    StreamSocket::write($resource, $error."\n");
                    $remote->close();
                    return;
                }
            }
            else
            {
                $body = $remote->getAttribute("body");

                $str = "";
                $n = FCGIUtil::read( $resource, $fcgi, $str );
                $body .= $str;

                if( $fcgi->inLen != 0 )
                {
                    $remote->setAttribute("body", $body);
                    return;
                }

                if( $fcgi->env['REQUEST_URI'] == "" )
                {
                    Console::warn("REQUEST_URI undefined");
                    $remote->close();
                    return;
                }

                $server = $remote->getAttribute('server');

                $netConn = new NetConnection($remote, $server);

                $request = new FCGIServerRequest($fcgi, $body);
                $response = new FCGIServerResponse($fcgi, $netConn);

                $method     = $request->method;
                $requestURI = $request->requestURI;

                $route = $server->route;
                $route->dispatch($requestURI, $method, $request, $response);

                if( !$remote->isClosed() )
                    $server->emit("request", $request, $response);
            }
        })->listenResource($resource);
    }
}




