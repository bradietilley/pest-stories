<?php

namespace BradieTilley\Stories\Concerns;

use function BradieTilley\Stories\Helpers\story;
use Illuminate\Support\Arr;

trait Reposes
{
    /**
     * Get a shared variable in this story
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get(story()->data, $key, $default);
    }

    /**
     * Set a shared variable in this story
     */
    public function set(string $key, mixed $value): static
    {
        Arr::set(story()->data, $key, $value);

        return $this;
    }

    /**
     * Check the existence of a shared variable in this story
     */
    public function has(string $key): bool
    {
        return Arr::has(story()->data, $key);
    }

    /**
     * Get all shared variables in this story
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return story()->data;
    }

    /**
     * Merge the given data with the current data repository
     *
     * @param  array<mixed>  $data
     */
    public function merge(array $data): static
    {
        story()->data = array_replace(story()->data, $data);

        return $this;
    }
}
