<?php

namespace BradieTilley\Stories\Concerns;

use function BradieTilley\Stories\Helpers\story;
use Illuminate\Support\Arr;

trait Reposes
{
    /**
     * Get a shared variable in this story
     */
    public function getData(string $key, mixed $default = null): mixed
    {
        return Arr::get(story()->data, $key, $default);
    }

    /**
     * Set a shared variable in this story
     */
    public function setData(string $key, mixed $value): static
    {
        Arr::set(story()->data, $key, $value);

        return $this;
    }

    /**
     * Check the existence of a shared variable in this story
     */
    public function hasData(string $key): bool
    {
        return Arr::has(story()->data, $key);
    }

    /**
     * Get all shared variables in this story
     *
     * @return array<string, mixed>
     */
    public function allData(): array
    {
        return story()->data;
    }

    /**
     * Merge the given data with the current data repository
     *
     * @param  array<mixed>  $data
     */
    public function mergeData(array $data): static
    {
        story()->data = array_replace(story()->data, $data);

        return $this;
    }
}
