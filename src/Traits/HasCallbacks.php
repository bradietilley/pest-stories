<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story;
use Closure;
use Illuminate\Container\Container;
use InvalidArgumentException;

trait HasCallbacks
{
    /**
     * Registered callbacks
     */
    private array $registeredCallbacks = [];

    /**
     * Registered static callbacks
     */
    private static array $registeredStaticCallbacks = [];

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
     * 
     * @return $this
     */
    public function setCallback(string $name, ?Closure $callback): self
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

    public function inheritCallbacks(): void
    {
        /** @var HasCallbacks|HasInheritance $this */
        $all = [];

        foreach (array_reverse($this->getAncestors()) as $level) {
            $callbacks = $level->getProperty('registeredCallbacks');

            foreach ($callbacks as $name => $callback) {
                if ($callback === null) {
                    continue;
                }

                $all[$name] = $callback;
            }
        }

        $this->registeredCallbacks = $all;
    }
}
