<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Contracts;

/**
 * Supply some deferred context to the given object (story)
 * through means of setting existing cache entries, session
 * values, or configuration.
 *
 * @mixin WithInheritance
 */
interface WithPendingContext
{
    /**
     * Set some cache when this object is ran
     *
     * Deferred proxy to
     *      Cache::set($key, $value)
     */
    public function setCache(string|array $key, mixed $value = null): static;

    /**
     * Set some config when this object is ran
     *
     * Deferred proxy to
     *      Config::set($key, $value)
     */
    public function setConfig(string|array $key, mixed $value = null): static;

    /**
     * Set some session values when this object is ran
     *
     * Deferred proxy to
     *      Session::set($key, $value)
     */
    public function setSession(string|array $key, mixed $value = null): static;

    /**
     * Inherit any pending context data
     */
    public function inheritPendingContext(): void;

    /**
     * Boot the pending context data
     */
    public function bootPendingContext(): void;

    /**
     * Get all registered config
     */
    public function allPendingContext(): array;
}
