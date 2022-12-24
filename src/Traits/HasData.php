<?php

namespace BradieTilley\StoryBoard\Traits;

trait HasData
{
    protected array $data = [];

    /**
     * Set a variable
     */
    public function setData(string $key, mixed $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Get a variable
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