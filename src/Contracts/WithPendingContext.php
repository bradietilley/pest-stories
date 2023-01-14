<?php

namespace BradieTilley\StoryBoard\Contracts;

interface WithPendingContext
{
    /**
     * Set some cache when this object is ran
     */
    public function setCache(string|array $key, mixed $value): static;

    /**
     * Set some config when this object is ran
     */
    public function setConfig(string|array $key, mixed $value): static;

    /**
     * Set some session values when this object is ran
     */
    public function setSession(string|array $key, mixed $value): static;

    /**
     * Inherit any config and session data
     */
    public function inheritPendingContext(): void;

    /**
     * Boot the config and session data
     */
    public function bootPendingContext(): void;

    /**
     * Get all registered config
     */
    public function allPendingContext(): array;
}
