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
     * Get all data
     */
    public function allData(): array
    {
        return $this->data;
    }
}