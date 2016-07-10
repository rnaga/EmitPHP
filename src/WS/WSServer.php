<?php

namespace Emit\WS;

use Emit\ServerResponse;
use Emit\Server;
use Emit\NetConnection;
use Emit\StreamSocket;
use Emit\Event\ResourceEventEmitter;
use Emit\HTTP\HTTPUtil;
use Emit\HTTP\HTTPServerRequest;
use Emit\Router\PathMatcher;
use Emit\Console;

class WSServer extends Server
{
    private static $defaultAppName = "__default__";
    private $apps;

    function __construct()
    {
        parent::__construct();

        $this->apps = [];

        $this->on('accept', function($server, $resource) {

            $remote = new ResourceEventEmitter();

            $remote->on("read", function($remote, $resource) use ($server) {

               $request = $remote->getAttribute("request", function(){
                    return WSUtil::initRequest();
                });
        
                $resource = $remote->getResource();
        
                $r = HTTPUtil::readRequest($resource, $request);
        
                if( $r === false )
                {
                    if( !is_null( $request->error ) )
                    {
                        Console::warn($request->error);
                        $this->emit("error", $remote, $resource);
                        $remote->close();
                    }
        
                    return;
                }
        
                if( $server->has('before_connect') )
                    $server->emit('before_connect', $server, $request);

                if( !is_null( $request->appName ) )
                    $appName = $request->appName;
                else
                    // Get the appName from SCRIPT_NAME
                    $appName = PathMatcher::filter($request->scriptName);

                $app = $this->getApp($appName);
        
                if( $app === null )
                {
                    Console::warn("App Not Found $appName");
                    $remote->close();
                    return;
                }
        
                $request->setWSHeaders();
       
                $netConn  = new WSNetConnection($remote, $this, $app);
                $response = new WSServerResponse($request, $netConn);

                $response->unsetHeader('Content-Type');

                // Only supports RFC6455, version 13
/*
                if( !isset( $request->secWebSocket['Version'] ) ||
                    false === strpos( $request->secWebSocket['Version'], '13' ) )
                {
                    $response->status(400);
                    $response->send();
                    $remote->close();
                    return;   
                }
*/
                $this->emit("connect", $request, $response);

                if( !is_null( $request->error ) )
                {
                    Console::warn("Error: " . $request->error);
                    $remote->close();
                    return;
                }

                $response->send();
                $app->addClient($remote, $netConn);

            })->listenResource($resource);
        });
    }

    public function app(string $appName = '')
    {
        if( $appName == '' )
            $appName = self::$defaultAppName;
        else if( !preg_match( '/^[a-z][a-z0-9\/\-]+$/', $appName ) )
        {
            Console::error("Illegal appName: $appName");
            return null;
        }

        $appName = PathMatcher::filter($appName);

        if( $appName == '' )
            return null;

        if( isset( $this->apps[$appName] ) )
            return $this->apps[$appName];

        $app = new WSApplication($appName);
        $this->apps[$appName] = $app;

        return $app;
    }

    public function getApp(string $appName)
    {
        if( $appName === '' )
            $appName = self::$defaultAppName;

        if( !isset( $this->apps[$appName] ) )
        {
            Console::error("App Not Found: $appName");
            return null;
        }

        return  $this->apps[$appName];
    }

}






