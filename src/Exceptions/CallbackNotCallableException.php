<?php

namespace BradieTilley\Stories\Exceptions;

use BradieTilley\Stories\Helpers\ReflectionCallback;
use Closure;
use Exception;
use Throwable;

class CallbackNotCallableException extends Exception
{
    public static function make(string|array|Closure|callable $callback, Throwable $throwable): self
    {
        return new self(
            self::generateMessage($callback),
            previous: $throwable,
        );
    }

    public static function generateMessage(string|array|Closure|callable $callback): string
    {
        return sprintf(
            'Cannot call non-callable callback: %s',
            self::getType($callback),
        );
    }

    public static function getType(string|array|Closure|callable $callback): string
    {
        if (is_string($callback)) {
            return sprintf('function: `%s()`', $callback);
        }

        if (is_array($callback)) {
            if (count($callback) === 2) {
                $object = $callback[0] ?? null;
                $method = $callback[1] ?? null;

                if ((is_string($object) || is_object($object)) && is_string($method)) {
                    if (is_object($object)) {
                        $object = get_class($object);
                    }

                    return sprintf('method: `%s::%s()`', $object, $method);
                }
            }

            return 'method: <unknown array format>';
        }

        $reflection = ReflectionCallback::make($callback);

        return sprintf(
            '%s:%d',
            $reflection->reflection()->getFileName(),
            $reflection->reflection()->getStartLine(),
        );
    }
}