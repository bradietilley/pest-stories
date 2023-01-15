<?php

namespace BradieTilley\StoryBoard\Traits;

/**
 * This object has a variables/data container that you can
 * write to and read from
 *
 * *
 * @mixin \BradieTilley\StoryBoard\Contracts\WithInheritance
 */
trait HasData
{
    /**
     * Container of all variables from action
     */
    protected array $data = [];

    /**
     * Set a variable or action result
     */
    public function setData(string|array $key, mixed $value = null): static
    {
        if (is_array($key)) {
            $this->data = array_replace($this->data, $key);

            return $this;
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Get a variable or action result
     */
    public function getData(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Has the given variable been set?
     */
    public function hasData(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get all data
     */
    public function allData(): array
    {
        return $this->data;
    }

    /**
     * Alias for ::setData($key, $value)
     */
    public function set(string|array $key, mixed $value = null): static
    {
        return $this->setData($key, $value);
    }

    /**
     * Alias for ::getData($key, $default)
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getData($key, $default);
    }

    /**
     * Alias for ::hasData($key)
     */
    public function has(string $key): bool
    {
        return $this->hasData($key);
    }

    /**
     * Alias for ::allData()
     */
    public function all(): array
    {
        return $this->allData();
    }

    /**
     * Inherit all data from parent stories
     */
    public function inheritData(): void
    {
        $all = [];

        foreach (array_reverse($this->getAncestors()) as $level) {
            $all = array_replace($all, (array) $level->getProperty('data'));
        }

        $this->data = $all;
    }
}
