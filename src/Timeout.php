<?

namespace Emit;

use Emit\Event\GlobalEventEmitter;

class Timeout
{
    public static function set($function, int $timeout, ...$args)
    {
        GlobalEventEmitter::setTimeout($function, $timeout, false, ...$args);
    }

    public static function interval($function, int $timeout, ...$args)
    {
        GlobalEventEmitter::setTimeout($function, $timeout, true, ...$args);
    }
}

