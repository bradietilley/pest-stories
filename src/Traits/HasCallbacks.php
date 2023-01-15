<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Contracts\WithInheritance;
use BradieTilley\StoryBoard\Story;
use Closure;
use Illuminate\Container\Container;

/**
 * This object has custom callbacks (e.g. before, booting, etc) that
 * you can add callbacks to.
 *
 * @mixin \BradieTilley\StoryBoard\Contracts\WithInheritance
 */
trait HasCallbacks
{
    /**
     * Registered callbacks
     */
    protected array $registeredCallbacks = [];

    /**
     * Registered static callbacks
     */
    protected static array $registeredStaticCallbacks = [];

    /**
     * Get Laravel's container
     */
    public static function getContainer(): Container
    {
        return Container::getInstance();
    }

    /**
     * Call the given closure with the given args
     */
    public static function call(callable $callback, array $arguments): mixed
    {
        return static::getContainer()->call($callback, $arguments);
    }

    /**
     * Call the given closure (if provided) with the given args
     */
    public static function callOptional(?callable $callback, array $arguments = []): mixed
    {
        if ($callback === null) {
            return null;
        }

        return static::call($callback, $arguments);
    }

    /**
     * Set a callback
     */
    public function setCallback(string $name, ?Closure $callback): static
    {
        $this->registeredCallbacks[$name] = $callback;

        return $this;
    }

    /**
     * Call a callback with the given args
     */
    public function runCallback(string $name, array $args = []): mixed
    {
        if (! isset($args['story']) && ($this instanceof Story)) {
            $args['story'] = $this;
        }

        return static::callOptional($this->getCallback($name), $args);
    }

    /**
     * Get a callback
     */
    public function getCallback(string $name): ?Closure
    {
        return $this->registeredCallbacks[$name] ?? null;
    }

    /**
     * Check a callback's existence
     */
    public function hasCallback(string $name): bool
    {
        return isset($this->registeredCallbacks[$name]);
    }

    /**
     * Set a static callback
     */
    public static function setStaticCallback(string $name, ?Closure $callback): void
    {
        static::$registeredStaticCallbacks[$name] = $callback;
    }

    /**
     * Call a static callback with the given args
     */
    public static function runStaticCallback(string $name, array $args = []): mixed
    {
        return static::callOptional(static::getStaticCallback($name), $args);
    }

    /**
     * Get a static callback
     */
    public static function getStaticCallback(string $name): ?Closure
    {
        return static::$registeredStaticCallbacks[$name] ?? null;
    }

    /**
     * Check a static callback's existence
     */
    public static function hasStaticCallback(string $name): bool
    {
        return isset(static::$registeredStaticCallbacks[$name]);
    }

    /**
     * Inherit all callbacks from this items's parents
     */
    public function inheritCallbacks(): void
    {
        if (! $this instanceof WithInheritance) {
            return;
        }

        $all = [];

        foreach (array_reverse($this->getAncestors()) as $level) {
            $callbacks = (array) $level->getProperty('registeredCallbacks');

            foreach ($callbacks as $name => $callback) {
                if ($callback === null) {
                    continue;
                }

                $all[$name] = $callback;
            }
        }

        $this->registeredCallbacks = $all;
    }

    /**
     * Get parameters available for DI callbacks
     */
    public function getParameters(array $additional = []): array
    {
        return $additional;
    }
}
