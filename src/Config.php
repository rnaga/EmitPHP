<?php

namespace Emit;

use Emit\Attribute;

class Config
{
    private $attr;

    private function __construct(array $arr)
    {
        $this->attr = new Attribute();

        foreach( $arr as $key => $value )
        {
            $this->attr->setAttribute($key, $value);
        }
    }

    public static function read(array $arr)
    {
        $config = new Config($arr);
        return $config;
    }

    public static function readFile(string $file, string $var)
    {
        if( !is_readable( $file ) )
            return null;

        require_once($file);

        if( isset( $$var ) && is_array( $$var ) )
            return self::read($$var); 

        return null;
    }

    public function get($key)
    {
        return $this->attr->getAttribute($key);
    }
    
}
