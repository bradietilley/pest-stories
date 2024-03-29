<?php

namespace BradieTilley\Stories\Exceptions;

use BradieTilley\Stories\Helpers\ReflectionCallback;
use Closure;
use Exception;
use Throwable;

class CallbackNotCallableException extends Exception
{
    /**
     * @param  string|array<string|object>|Closure|callable  $callback
     */
    public static function make(string|array|Closure|callable $callback, Throwable $throwable): self
    {
        return new self(
            self::generateMessage($callback),
            previous: $throwable,
        );
    }

    /**
     * @param  string|array<string|object>|Closure|callable  $callback
     */
    public static function generateMessage(string|array|Closure|callable $callback): string
    {
        return sprintf(
            'Cannot call non-callable callback: %s',
            ReflectionCallback::make($callback)->exceptionName(),
        );
    }
}
