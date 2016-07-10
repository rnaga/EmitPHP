<?php

namespace Emit\HTTP;

use Emit\ServerResponse;
use Emit\Server;
use Emit\NetConnection;
use Emit\StreamSocket;
use Emit\Router\Route;
use Emit\Event\ResourceEventEmitter;
use Emit\Console;

class HTTPServer extends Server
{
    // Route -> HTTPRoute
    protected $route;

    public function route()
    {
        return new HTTPRoute();
    }

    public function use(...$args)
    {
        if( !$args[0] instanceof HTTPRoute && 
            !$args[1] instanceof HTTPRoute )
        {
            Console::error("Invalid arguments");
            return null;
        }

        $this->route->use(...$args);
    }

    public static function cookieParser($request, $response, $next)
    {
        $cookie = HTTPUtil::getHeaderValue('COOKIE', $request->rawHeaders);
        $request->cookie = HTTPUTil::cookieToArray($cookie);
        $next();
    }

    protected function onAccept($server, $resource)
    {
        $remote = new ResourceEventEmitter();

        $remote->on("read", function($remote, $resource) use ($server) {

            $request = $remote->getAttribute("request", function(){
                return HTTPUtil::initRequest();
            });

            $r = HTTPUtil::readRequest($resource, $request);

            if( $r === false )
            {
                if( !is_null( $request->error ) )
                {
                    Console::warn($request->error);
                    $server->emit("error", $remote, $resource);
                    $remote->close();
                }

                return;
            }

            $netConn = new NetConnection($remote, $server);
            $response = new HTTPServerResponse($request, $netConn);

            $method     = $request->method;
            $requestURI = $request->requestURI;

            $route = $server->route;
            $route->dispatch($requestURI, $matches, $method, $request, $response);

            if( !$remote->isClosed() )
                $server->emit("request", $request, $response);

        })->listenResource($resource);
    }

    protected function onClose($netConn)
    {
        $this->emit('close', $netConn);
    }

    function __construct()
    {
        parent::__construct();

        $self = $this;
        $route = new Route();

        $this->route = $route;

        $this->on('client_close', function($netConn) use ($self){
            $self->onClose($netConn);
        });

        $this->on('accept', function($server, $resource) use ($self){
            $self->onAccept($server, $resource);
        });
    }
}




