<?php

namespace Emit\Router;

use Emit\Event\EventEmitter;

class Route extends EventEmitter
{
    public $vals;

    // Create sub-Route
    public static function route()
    {
        $route = new Route();
        return $route;
    }

    // Can be overridden
    protected function isValidHandler()
    {
        return true;
    }

    // Can be overridden
    protected function getMatcher($pattern = null)
    {
        return PathMatcher::compile($pattern);
    }

    // Can be overridden
    public function sub(Matcher $matcher, $subject, $matches)
    {
       $newSubject = $matcher->substr($subject, $matches);
       return $newSubject;
    }

    // Add a sub route
    public function use(...$args)
    {
        if(  0 >= count($args) )
            return false;

        $moreArgs = [];

        if( 1 == count( $args ) )
        {
            $pattern = null; 
            $handler = $args[0];
        }
        else
        {
            $pattern = $args[0];
            $handler = $args[1];
        }

        if( !$handler instanceof Router &&
            !$this->isValidHandler($handler) &&
            !is_callable( $handler ) )
        {
            return false;
        }

        if( $pattern instanceof Matcher )
            $matcher = $pattern;
        else
            $matcher = $this->getMatcher($pattern);

        if( !$matcher instanceof Matcher )
        {
            return false;
        }

        if( is_callable( $handler ) && count( $args ) > 2 )
        {
            $moreArgs = array_slice($args, 2);
        }
        
        $this->vals[] = array
        (
            'matcher'   => $matcher,
            'handler'   => $handler,
            'more_args' => $moreArgs,
        );

        return true;
    }

    public function dispatch($subject, &$matches, ...$args)
    {
        $vals = $this->vals;
        $self = $this;

        $next = function() use ($self, &$next, $subject, $args, &$vals, &$matches)
        {
            if( !count( $vals ) ) return;

            $nextVal = array_shift($vals);
        
            $matcher  = $nextVal['matcher'];
            $handler  = $nextVal['handler'];
            $moreArgs = $nextVal['more_args'];

            if( $matcher->matches( $subject, $matches ) )
            {
                if( $handler instanceof Route )
                {
                    $handler->dispatch($self->sub($matcher, $subject, $matches), $matches, ...$args);
                    $next();
                    return;
                }

                if( count( $moreArgs ) > 0 )
                    $args = array_merge($args, $moreArgs);

                $args[] = $next;

                $this->emit("matches", $matches, $handler, ...$args);
                return;
            }
                
            $next();
        };

        $next();
    }
}


