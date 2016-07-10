<?php

namespace Emit;

class Attribute
{
    protected $attr = null;

    final public function setAttribute($key, $value)
    {
        $this->attr[$key] = $value;
    }

    final public function getAttribute($key, $funcOrValue = null, ...$args)
    {
        if( !isset( $this->attr[$key] ) )
        {
            if( is_callable( $funcOrValue ) )
                $this->attr[$key] = $funcOrValue(...$args);
            else
                $this->attr[$key] = $funcOrValue;
        }

        return $this->attr[$key];
    }

    final public function setAndGetAttribute($key, $funcOrValue = null, ...$args)
    {
        $value = $this->getAttribute($key);

        if( is_null( $attr ) )
        {
            $value = $this->getAttribute($key, $funcOrValue, ...$args);
            if( is_null( $value ) ) return null;
        }

        $this->setAttribute($key, $value);
        return $value;
    }

    final public function unsetAttribute($key)
    {
        if( !isset( $this->attr[$key] ) )
            unset($this->attr[$key]);
    }

    // jQuery Like
    final public function attr($key, $value = null)
    {
        if( is_null( $value ) )
        {
            return $this->getAttribute($key);
        }

        $this->setAttribute($key, $value);
        return $this->getAttribute($key);
    }
}


