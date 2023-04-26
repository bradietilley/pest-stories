<?php

namespace BradieTilley\Stories;

use ArrayAccess;
use BradieTilley\Stories\Exceptions\DatasetVariableUnavailableException;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @property array<int, mixed> $dataset
 */
class Dataset implements Arrayable, ArrayAccess
{
    /**
     * @param  array<int, mixed>  $dataset
     */
    public function __construct(protected array $dataset)
    {
    }

    /**
     * Check if this dataset has the given index
     */
    public function has(int $index): bool
    {
        return array_key_exists($index, $this->dataset);
    }

    /**
     * Get the nth dataset variable for the given test
     */
    public function get(int $index): mixed
    {
        if (! $this->has($index)) {
            throw DatasetVariableUnavailableException::make($index);
        }

        return $this->dataset[$index];
    }

    /**
     * Get the nth dataset variable for the given test
     */
    public function set(int $index, mixed $value): self
    {
        $this->dataset[$index] = $value;

        return $this;
    }

    /**
     * @return array<int, mixed>
     */
    public function all(): array
    {
        return $this->dataset;
    }

    /**
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        return $this->all();
    }

    public function offsetExists(mixed $offset): bool
    {
        if (! is_int($offset)) {
            return false;
        }

        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (! is_int($offset)) {
            return null;
        }

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (! is_int($offset)) {
            return;
        }

        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        if (! is_int($offset)) {
            return;
        }

        $this->set($offset, null);
    }
}
