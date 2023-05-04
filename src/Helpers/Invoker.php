<?php

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Contracts\Invoker as ContractsInvoker;
use Closure;
use Illuminate\Support\Collection;
use Throwable;

class Invoker implements ContractsInvoker
{
    public static function make(): self
    {
        return new self();
    }

    /**
     * Invoke the given callback with the given parameters (depending on what's been requested)
     *
     * @param  array<string>|string|Closure|callable  $callback
     * @param  array<mixed>  $parameters
     */
    public function call(array|string|Closure|callable $callback, array $parameters = [], ?string $defaultMethod = null): mixed
    {
        $args = self::arguments($callback, $parameters);

        try {
            /** @var callable $callback */
            return $callback(...$args);
        } catch (Throwable $e) {
            dd($e, $callback, $args);
        }
    }

    /**
     * Parse what arguments are required for this method
     *
     * @param  array<string>|string|Closure|callable  $callback
     * @param  array<mixed>  $parameters
     * @return array<int, mixed>
     */
    public static function arguments(array|string|Closure|callable $callback, array $parameters = []): array
    {
        $reflection = ReflectionCallback::make($callback);

        $arguments = Collection::make($reflection->arguments())
            ->map(fn (string $key) => $parameters[$key])
            ->toArray();

        /** @var array<int, mixed> $arguments */
        return $arguments;
    }
}
