<?php

namespace BradieTilley\Stories\Concerns;

use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Repositories\DataRepository;
use BradieTilley\Stories\Story;

trait Reposes
{
    /**
     * Get the data repository to use for this story or action
     */
    private function getStoryRepository(): DataRepository
    {
        if ($this instanceof Story) {
            return $this->data;
        }

        return story()->data;
    }

    /**
     * Get a shared variable in this story
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getStoryRepository()->getOr($key, $default);
    }

    /**
     * Set a shared variable in this story
     */
    public function set(string $key, mixed $value): static
    {
        $this->getStoryRepository()->set($key, $value);

        return $this;
    }

    /**
     * Check the existence of a shared variable in this story
     */
    public function has(string $key): bool
    {
        return $this->getStoryRepository()->has($key);
    }

    /**
     * Get all shared variables in this story
     *
     * @return array<mixed>
     */
    public function all(): array
    {
        return $this->getStoryRepository()->all();
    }

    /**
     * Merge the given data with the current data repository
     *
     * @param  array<mixed>  $data
     */
    public function merge(array $data): static
    {
        $this->getStoryRepository()->merge($data);

        return $this;
    }
}
