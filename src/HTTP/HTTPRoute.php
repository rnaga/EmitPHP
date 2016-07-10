<?php

namespace Emit\HTTP;

use Emit\ServerResponse;
use Emit\Server;
use Emit\Router\Route;

class HTTPRoute extends Route
{
    // Using composition
    private $route;

    public function __construct()
    {
        $this->route = [];

        foreach(['POST', 'GET', 'DELETE', 'HEAD', 'PUT', 'UPDATE'] as $method )
        {
            $this->route[$method] = new Route();
        }
    }

    public static function route()
    {
        return new Route();
    }

    public function method($method, ...$args)
    {
        if( !isset( $this->route[$method] ) )
        {
            return $this;
        }

        $route = $this->route[$method];
        $route->use(...$args);
        return $this;
    }

    public function all(...$args)
    {
        foreach(['POST', 'GET', 'DELETE', 'HEAD', 'PUT', 'UPDATE'] as $method )
        {
            $this->method($method, ...$args);
        }

        return $this;
    }

    public function post(...$args)
    {
        return $this->method('POST', ...$args);
    }

    public function put(...$args)
    {
        return $this->method('PUT', ...$args);
    }

    public function get(...$args)
    {
        return $this->method('GET', ...$args);
    }

    public function update(...$args)
    {
        return $this->method('UPDATE', ...$args);
    }

    public function delete(...$args)
    {
        return $this->method('DELETE', ...$args);
    }

    public function dispatch($path, &$matches, ...$args)
    {
        $method   = $args[0];
        $request  = $args[1];
        $response = $args[2];

        $route = $this->route[$method];

        $route->on("matches", function($matches, $handler, $request, $response, $next){
            $request->params = $matches;
            $args = [$request, $response, $next];
            $handler(...$args);
        });

        $route->dispatch($path, $matches, $request, $response);
        $route->unset("matches");
    }

    public function use(...$args)
    { 
        return $this->all(...$args);;
    }
}







