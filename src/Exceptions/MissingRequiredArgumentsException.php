<?php

namespace BradieTilley\Stories\Exceptions;

use Closure;
use Exception;
use Illuminate\Support\Str;
use Throwable;

class MissingRequiredArgumentsException extends Exception
{
    /**
     * @param  string|array<string|object>|Closure|callable  $callback
     */
    public static function make(string|array|Closure|callable $callback, Throwable $throwable): self
    {
        return new self(
            self::generateMessage($callback, $throwable),
            previous: $throwable,
        );
    }

    /**
     * @param  string|array<string|object>|Closure|callable  $callback
     */
    public static function generateMessage(string|array|Closure|callable $callback, Throwable $throwable): string
    {
        return sprintf(
            'Missing required arguments for callback invocation: %s',
            Str::of($throwable->getMessage())->replaceMatches('/ given, called in .+$/', ' given'),
        );
    }
}
