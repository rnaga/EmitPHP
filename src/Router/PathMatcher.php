<?php

namespace Emit\Router;

use Emit\Console;

class PathMatcher implements Matcher
{
    private static $validFolderRegex = "/^[:a-zA-Z0-9\-_%\+]+$/";
    private static $varMappingRegex  = "/^:([a-zA-Z]+(?:[a-zA-Z0-9_]*))$/";
    private static $defaultRegex     = "([a-zA-Z\-_%0-9\.]+)"; 
    private $pattern;
    private $isRegex;
    private $varMapping;

    private function __construct()
    {
        $this->isRegex = false;
        $this->varMapping = [];
    }

    private static function isValidRegex(string $regex)
    {
        if( false === @preg_match($regex, '' ) )
            return false;

        return true;
    }

    public static function filter(string $path)
    {
        return trim(preg_replace("/\/[\/]+/", "/", $path), "/");
    }

    public static function regex(string $regex)
    {
        $regex = "/^" . str_replace( '/', "\\/", $regex ) . "/";

        if( !self::isValidRegex($regex) )
        {
            Console::error("Invalid regex: $regex");
            return false;
        }

        $matcher = new PathMatcher();

        $matcher->isRegex = true;
        $matcher->pattern = $regex; 

        return $matcher;
    }

    public static function compile($params = null)
    {
        if( is_null( $params ) ) $params = '';

        // Only accepts string or array
        if( !is_string($params) && !is_array($params) )
            return null;

        if( is_string($params) )
        {
            $path = $params;
            $regexes = [];
        }
        else // array
        {
            if( 2 < count($params) || !is_string($params[0]) )
                return null;

            $path    = $params[0];
            $regexes = isset($params[1]) ? $params[1] : [];
        }

        $matcher = new PathMatcher();

        // Clean up slashes
        $matcher->pattern = $path = self::filter($path);

        if( $path == '' )
            return $matcher;

        $pathArr    = explode("/", $path);
        $varMapping = []; 
        $isRegex    = false;

        for( $i = 0, $matches = []; $i < count( $pathArr ); $i++, $matches = [] )
        {
            $value = $pathArr[$i];

            if( !@preg_match( self::$validFolderRegex, $value ) )
            {
                Console::error("Invalid char $value");
                return null;
            } 

            if( $value[0] == ":" )
            {
                if( false === @preg_match( self::$varMappingRegex, $value, $matches ) ||
                    !isset( $matches[1] ) )
                {
                    Console::error("Error $value");
                    return null;
                }

                $var = $matches[1];
                $varMapping[] = $var;

                if( isset( $regexes[$var] ) )
                    $pathArr[$i] = "(" . $regexes[$var] . ")";
                else
                    $pathArr[$i] = self::$defaultRegex;

                $isRegex = true;
            }
        }

        if( !$isRegex )
        {
            $matcher->pattern = implode("/", $pathArr );
            return $matcher;
        }

        $regex = "/^" . implode("\/", $pathArr) . "/";

        if( !self::isValidRegex( $regex ) )
        {
            Console::error("Invalid regex: $regex");
            return null;
        }

        $matcher->isRegex    = $isRegex;
        $matcher->varMapping = $varMapping;
        $matcher->pattern    = $regex; 
      
        return $matcher;
    }

    public function substr($subject, $matches)
    {
       $subject = self::filter($subject);
       $str = $matches[0];
       $newSubject = substr($subject, strlen($str));
       return $newSubject;
    }

    public function matches($subject, &$matches)
    {
        // Accepts all paths
        if( $this->pattern == '' )
        {
            $matches[0] = '';
            return true;
        }

        $subject = self::filter($subject);

        if( !$this->isRegex )
        {
            if( strlen($subject) >= strlen($this->pattern) &&
                0 === strpos( $subject, $this->pattern ) )
            {
                $matches[0] = $this->pattern;
                return true;
            }

            return false;
        }
       
        $tmpMatches = [];
 
        $r = preg_match($this->pattern, $subject, $tmpMatches); 

        if( $r == true && is_array( $this->varMapping ) )
        {
            foreach( $tmpMatches as $i => $val )
            {
                $matches[$i] = $tmpMatches[$i];
            }

            for( $i = 0; $i < count($this->varMapping); $i++ )
            {
                $var = $this->varMapping[$i];
                $matches[$var] = $tmpMatches[$i+1];
                unset( $matches[$i+1] );
            }

            // Store the raw result
            $matches['__regex__'][] = $tmpMatches;
        }
        
        return $r;
    }
}



