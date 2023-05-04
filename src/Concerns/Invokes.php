<?php

namespace BradieTilley\Stories\Concerns;

use BradieTilley\Stories\Contracts\Invoker;
use BradieTilley\Stories\Helpers\Invoker as HelpersInvoker;
use Closure;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Container\Container;

/**
 * Choose what is responsible for invoking callbacks like actions.
 *
 * The Invoker will be responsible for providing the necessary arguments
 * when invoking the callback.
 *
 * Null will default to use Laravel's container, which has the same `call()`
 * method signature.
 */
trait Invokes
{
    /** The invoker instance to use when invoking callables */
    protected static Invoker|null $invokeUsing = null;

    /**
     * Replace the injectable instance with the given one, or default
     * to use Laravel's Container by passing null
     */
    public static function invokeUsing(?Invoker $invoker): void
    {
        static::$invokeUsing = $invoker;
    }

    /**
     * Get the injector to use
     */
    public function invoker(): Invoker|Container
    {
        return static::$invokeUsing ?? Application::getInstance();
    }

    /**
     * Call the given callback with dependency injection
     *
     * @param  array<string|object>|string|Closure|callable  $callback
     * @param  array<string, mixed>  $additional
     */
    public function call(array|string|Closure|callable $callback = null, array $additional = []): mixed
    {
        if ($callback === null) {
            return null;
        }

        $arguments = $this->getCallbackArguments($additional);

        /** @var Invoker|Container|HelpersInvoker $invoker */
        $invoker = $this->invoker();

        return $invoker->call($callback, $arguments);
    }

    /**
     * Get a list of arguments that may be injected into Closure callbacks
     *
     * @param  array<mixed>  $additional
     * @return array<mixed>
     */
    public function getCallbackArguments(array $additional = []): array
    {
        // @codeCoverageIgnoreStart
        return $additional;
        // @codeCoverageIgnoreEnd
    }
}
