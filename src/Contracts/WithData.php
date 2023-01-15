<?php

namespace BradieTilley\StoryBoard\Contracts;

/**
 * This object has a variables/data container that you can
 * write to and read from
 *
 * @mixin WithInheritance
 */
interface WithData
{
    /**
     * Set a variable or action result
     */
    public function setData(string|array $key, mixed $value = null): static;

    /**
     * Get a variable or action result
     */
    public function getData(string $key, mixed $default = null): mixed;

    /**
     * Has the given variable been set?
     */
    public function hasData(string $key): bool;

    /**
     * Get all data
     */
    public function allData(): array;

    /**
     * Alias for ::setData($key, $value)
     */
    public function set(string|array $key, mixed $value = null): static;

    /**
     * Alias for ::getData($key, $default)
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Alias for ::hasData($key)
     */
    public function has(string $key): bool;

    /**
     * Alias for ::allData()
     */
    public function all(): array;

    /**
     * Inherit all data from parent stories
     */
    public function inheritData(): void;
}
