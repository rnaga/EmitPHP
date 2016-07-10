<?php

namespace Emit\Router;

interface Matcher
{
    static function compile($params);
    function matches($subject, &$matches);
}
