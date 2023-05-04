<?php

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Contracts\Invoker as ContractsInvoker;
use BradieTilley\Stories\Exceptions\CallbackNotCallableException;
use BradieTilley\Stories\Exceptions\MissingRequiredArgumentsException;
use Closure;
use Illuminate\Support\Collection;
use Throwable;
use TypeError;

class Invoker implements ContractsInvoker
{
    public const INVOKES_FROM_LINE = 34;

    public static function make(): self
    {
        return new self();
    }

    /**
     * Invoke the given callback with the given parameters (depending on what's been requested)
     *
     * @param  array<string|object>|string|Closure|callable  $callback
     * @param  array<mixed>  $parameters
     */
    public function call(array|string|Closure|callable $callback, array $parameters = [], ?string $defaultMethod = null): mixed
    {
        $args = self::arguments($callback, $parameters);

        try {
            /** @var callable $callback */
            return $callback(...$args);
        } catch (TypeError $error) {
            if (! $this->relevantTypeError($error)) {
                throw $error;
            }

            throw MissingRequiredArgumentsException::make($callback, $error);
        } catch (Throwable $throwable) {
            throw CallbackNotCallableException::make($callback, $throwable);
        }
    }

    /**
     * Parse what arguments are required for this method
     *
     * @param  array<string|object>|string|Closure|callable  $callback
     * @param  array<mixed>  $parameters
     * @return array<int, mixed>
     */
    public static function arguments(array|string|Closure|callable $callback, array $parameters = []): array
    {
        $reflection = ReflectionCallback::make($callback);

        $arguments = Collection::make($reflection->arguments())
            ->map(fn (string $key) => $parameters[$key] ?? null)
            ->toArray();

        /** @var array<int, mixed> $arguments */
        return $arguments;
    }

    public function relevantTypeError(TypeError $error): bool
    {
        $trace = $error->getTrace()[0] ?? null;

        if ($trace === null) {
            return false;
        }

        if (($trace['file'] ?? '') !== __FILE__) {
            return false;
        }

        if (($trace['line'] ?? -1) !== self::INVOKES_FROM_LINE) {
            return false;
        }

        $message = $error->getMessage();
        $regex = '/Argument #\d+ \(\$[^\)]+\) must be of type .+, .+ given/';
        $relevant = (bool) preg_match($regex, $message);

        return $relevant;
    }
}
