<?php

namespace Emit\Event;

class Heap extends \SplHeap
{
    private $comparator;

    function __construct($comparator)
    {
        $this->comparator = $comparator;
    }

    public function compare($val1, $val2)
    {
        $comparator = $this->comparator;
        return $comparator($val1, $val2);
    }
}

