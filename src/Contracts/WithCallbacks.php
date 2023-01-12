<?php

namespace BradieTilley\StoryBoard\Contracts;

use Closure;
use Illuminate\Container\Container;

interface WithCallbacks
{
    /**
     * Get Laravel's container
     */
    public static function getContainer(): Container;

    /**
     * Call the given closure with the given args
     */
    public static function call(callable $callback, array $arguments): mixed;

    /**
     * Call the given closure (if provided) with the given args
     */
    public static function callOptional(?callable $callback, array $arguments = []): mixed;

    /**
     * Set a callback
     */
    public function setCallback(string $name, ?Closure $callback): static;

    /**
     * Call a callback with the given args
     */
    public function runCallback(string $name, array $args = []): mixed;

    /**
     * Get a callback
     */
    public function getCallback(string $name): ?Closure;

    /**
     * Check a callback's existence
     */
    public function hasCallback(string $name): bool;

    /**
     * Set a static callback
     */
    public static function setStaticCallback(string $name, ?Closure $callback): void;

    /**
     * Call a static callback with the given args
     */
    public static function runStaticCallback(string $name, array $args = []): mixed;

    /**
     * Get a static callback
     */
    public static function getStaticCallback(string $name): ?Closure;

    /**
     * Check a static callback's existence
     */
    public static function hasStaticCallback(string $name): bool;

    /**
     * Inherit all callbacks from this items's parents
     *
     * @requires WithInheritance
     *
     * @return void
     */
    public function inheritCallbacks(): void;

    /**
     * Get parameters to sent into callbacks
     */
    public function getParameters(array $with = []): array;
}
