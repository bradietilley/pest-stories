<?php

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Contracts\Invoker as ContractsInvoker;
use BradieTilley\Stories\Exceptions\CallbackNotCallableException;
use BradieTilley\Stories\Exceptions\MissingRequiredArgumentsException;
use Closure;
use Error;
use TypeError;

/**
 * Provides similar functionality to Laravel's Container instance
 * in terms of being able to call a callback and pass in arguments.
 *
 * Laravel's `Container::getInstance()->call()` is fantastic, but
 * Xdebugging into StoryInvoker's `call()` method is 1 "step-over"
 * for it to reach the invocation of the callback and perform a full
 * invocation within 0.003s (based on benchmark test). On the other
 * hand Xdebugging into Container's `call()` method is hundreds of
 * step-in's and step-over's, and performs a full inocation within
 * 0.006s (based on the same benchmark). Considering what it's doing,
 * that's great, but for this purpose we don't need anything fancy.
 *
 * You can always opt in to use Container by running the below
 * in your Pest.php file:
 *
 *     Story::invokeUsing(app());
 *     Story::invokeUsingLaravel();
 *     story()->usingLaravelInvoker();
 */
class StoryInvoker implements ContractsInvoker
{
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

        $arguments = [];

        foreach ($reflection->arguments() as $key) {
            $arguments[$key] = $parameters[$key] ?? null;
        }

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

        $message = $error->getMessage();
        $regex = '/Argument #\d+ \(\$[^\)]+\) must be of type .+, .+ given/';
        $relevant = (bool) preg_match($regex, $message);

        return $relevant;
    }
}
