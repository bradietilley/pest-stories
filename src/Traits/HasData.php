<?php

namespace BradieTilley\StoryBoard\Traits;

trait HasData
{
    /**
     * Container of all variables from scenario
     */
    protected array $data = [];

    /**
     * Set a variable or scenario result
     */
    public function setData(string $key, mixed $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Get a variable or scenario result
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
    public function set(string $key, mixed $value): static
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
}
