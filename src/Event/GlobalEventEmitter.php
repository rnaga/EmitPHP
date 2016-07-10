<?php

namespace Emit\Event;

final class GlobalEventEmitter
{
    public static $eventList;
    public static $eventTimeoutList = null;
    public static $eventResourceList;
    public static $resources = null;

    public static $counter = 0;

    public static function register(EventEmitter $ee)
    {
        $id = self::$counter++;

        self::$eventList[$id] = array
        (
            'ee'    => $ee,
            'emits' => 0,
            'id'    => $id,
        );

        return $id;
    }

    public static function notifyDestruct(EventEmitter $ee)
    {
        $id = $ee->id;
        unset( self::$eventList[$id] );
    }

    public static function destroy(EventEmitter &$ee)
    {
        $id = $ee->id;
        unset(self::$eventList[$id]);

        if( $ee instanceof ResourceEventEmitter )
        {
            $rId = $ee->getResource();

            unset( self::$resources[$rId] );
            unset( self::$eventResourceList[$rId] );

            $ee->emit("resource_closed", $rId);
        }
    }

    public static function addNewResource(ResourceEventEmitter $ree)
    {
        $resource = $ree->getResource();
        $rId = (int)$resource;

        self::$resources[$rId] = $resource; 
        self::$eventResourceList[$rId] = [$ree];
    }

    public static function setTimeout($function, int $timeout, bool $isLoop, ...$args)
    {
        if( !is_callable( $function ) ) return;

        $ee = new EventEmitter();
        $ee->on('timeout', $function);

        $timeoutVal = [
            'ee'          => $ee,
            'unixTimeout' => (time() + (int)($timeout/1000)),
            'timeout'     => $timeout,
            'isLoop'      => $isLoop,
            'args'        => $args,
        ];

        if( is_null( self::$eventTimeoutList ) )
        {
            // Using min-heap for sorting the timeout list
            self::$eventTimeoutList = new Heap(function($t1, $t2){
                $ut1 = $t1['unixTimeout'];
                $ut2 = $t2['unixTimeout'];

                return $ut2 - $ut1;
            });
        }

        // O(log n)
        self::$eventTimeoutList->insert($timeoutVal);
    }

    public static function loop($usleep = 200 * 1000)
    {
        while(1){

           if( is_null( self::$resources ) || !count( self::$resources ) )
           {
               usleep($usleep);
           }
           else
           {
               $readSockets = array_values(self::$resources);
               $selectTimeout = $usleep;
               $write = $except = null;

               $n = stream_select( $readSockets, $write, $except, 0, $selectTimeout );
    
               if( $n > 0 )
               {
                   foreach( $readSockets as $readSocket )
                   {
                       $rId = (int)$readSocket;
                       list($ree) = self::$eventResourceList[$rId];
                       $ree->emit("read", $ree, $readSocket);
                   }
                }
    
                unset( $readSockets );
                unset( $readSocket );
                unset( $socket );

                clearstatcache( );
            }

            if( is_null( self::$eventTimeoutList ) )
                continue;

            while( !self::$eventTimeoutList->isEmpty() )
            {
                // O(1)
                $timeoutVal = self::$eventTimeoutList->top();

                // ['ee', 'unixTimeout', 'timeout', 'isLoop', 'args']
                extract($timeoutVal);

                if( time() < $unixTimeout )
                    break;

                $r = $ee->emit('timeout', ...$args);

                // O(log n)
                self::$eventTimeoutList->extract();

                // Check to see if it's setInterval
                if( true === $r && $isLoop )
                {
                    // Reset timeout
                    $timeoutVal['unixTimeout'] = time() + ($timeout/1000);
                    self::$eventTimeoutList->insert($timeoutVal);
                }
            }
        }
    }

}



