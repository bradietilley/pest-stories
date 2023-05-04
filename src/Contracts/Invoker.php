<?php

namespace BradieTilley\Stories\Contracts;

use Closure;

interface Invoker
{
    /**
     * Call the given Closure / class+method and inject its dependencies.
     *
     * @param  array<string|object>|string|Closure|callable  $callback
     * @param  array<mixed>  $parameters
     */
    public function call(array|string|Closure|callable $callback, array $parameters = [], string $defaultMethod = null): mixed;
}
