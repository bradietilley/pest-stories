<?php

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Contracts\Invoker as ContractsInvoker;
use BradieTilley\Stories\Exceptions\CallbackNotCallableException;
use BradieTilley\Stories\Exceptions\MissingRequiredArgumentsException;
use Closure;
use Error;
use Illuminate\Support\Collection;
use TypeError;

class StoryInvoker implements ContractsInvoker
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
                // @codeCoverageIgnoreStart
                throw $error;
                // @codeCoverageIgnoreEnd
            }

            throw MissingRequiredArgumentsException::make($callback, $error);
        } catch (Error $error) {
            if (! $this->relevantVisibilityError($error)) {
                // @codeCoverageIgnoreStart
                throw $error;
                // @codeCoverageIgnoreEnd
            }

            throw CallbackNotCallableException::make($callback, $error);
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

    public function relevantVisibilityError(Error $error): bool
    {
        $file = $error->getFile();
        $line = $error->getLine();

        if ($file !== __FILE__) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        if ($line !== self::INVOKES_FROM_LINE) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $message = $error->getMessage();
        $regex = '/Call to (private|protected) method .+/';
        $relevant = (bool) preg_match($regex, $message);

        return $relevant;
    }

    public function relevantTypeError(TypeError $error): bool
    {
        $trace = $error->getTrace()[0] ?? null;

        if ($trace === null) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        if (($trace['file'] ?? '') !== __FILE__) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        if (($trace['line'] ?? -1) !== self::INVOKES_FROM_LINE) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $message = $error->getMessage();
        $regex = '/Argument #\d+ \(\$[^\)]+\) must be of type .+, .+ given/';
        $relevant = (bool) preg_match($regex, $message);

        return $relevant;
    }
}
