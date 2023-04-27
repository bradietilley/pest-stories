<?php

namespace BradieTilley\Stories;

use ArrayAccess;
use BradieTilley\Stories\Exceptions\DatasetVariableUnavailableException;
use Illuminate\Contracts\Support\Arrayable;
use Iterator;

/**
 * @property array<int, mixed> $dataset
 */
class Dataset implements Arrayable, ArrayAccess, Iterator
{
    private int $iteratorPosition = 0;

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
     *
     * @throws DatasetVariableUnavailableException if index is not set (if the value is null, no exception is thrown, null is returned)
     */
    public function get(int $index): mixed
    {
        if (! $this->has($index)) {
            throw DatasetVariableUnavailableException::make($index);
        }

        return $this->dataset[$index];
    }

    /**
     * Set the nth dataset variable for the given test case
     */
    public function set(int $index, mixed $value): self
    {
        $this->dataset[$index] = $value;

        return $this;
    }

    /**
     * Convert this to array (fetch all)
     *
     * @return array<int, mixed>
     */
    public function all(): array
    {
        return $this->dataset;
    }

    /**
     * Arrayable: Convert this to array (fetch all)
     *
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * ArrayAccess: check if the given dataset (by index) exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        if (! is_int($offset)) {
            return false;
        }

        return $this->has($offset);
    }

    /**
     * ArrayAccess: get the value of the given dataset by index
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (! is_int($offset)) {
            return null;
        }

        return $this->get($offset);
    }

    /**
     * ArrayAccess: set the value of the given dataset by index
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (! is_int($offset)) {
            return;
        }

        $this->set($offset, $value);
    }

    /**
     * ArrayAccess: unset the given dataset by index (sets value to null)
     */
    public function offsetUnset(mixed $offset): void
    {
        if (! is_int($offset)) {
            return;
        }

        $this->set($offset, null);
    }

    /**
     * Iterator: Get the value for the current position
     */
    public function current(): mixed
    {
        return $this->get($this->iteratorPosition);
    }

    /**
     * Iterator: Get the key for the current position
     */
    public function key(): mixed
    {
        return $this->iteratorPosition;
    }

    /**
     * Iterator: Iterate to the next
     */
    public function next(): void
    {
        $this->iteratorPosition++;
    }

    /**
     * Iterator: Reset the position back to the start
     */
    public function rewind(): void
    {
        $this->iteratorPosition = 0;
    }

    /**
     * Iterator: Check if the current position is valid
     */
    public function valid(): bool
    {
        return $this->has($this->iteratorPosition);
    }
}
