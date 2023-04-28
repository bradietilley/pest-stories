<?php

namespace BradieTilley\Stories\Repositories;

use ArrayAccess;
use BradieTilley\Stories\Exceptions\DataVariableUnavailableException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Iterator;

/**
 * @property array<mixed> $data
 */
class DataRepository implements Arrayable, ArrayAccess, Iterator
{
    private int $iteratorPosition = 0;

    /**
     * @param  array<mixed>  $data
     */
    public function __construct(protected array $data = [])
    {
    }

    /**
     * Check if this data has the given index
     */
    public function has(int|string $index): bool
    {
        return Arr::has($this->data, (string) $index);
    }

    /**
     * Get the nth data variable
     *
     * @throws DataVariableUnavailableException if index is not set (if the value is null, no exception is thrown, null is returned)
     */
    public function get(int|string $index): mixed
    {
        if (! $this->has($index)) {
            throw DataVariableUnavailableException::make($index);
        }

        return Arr::get($this->data, $index);
    }

    /**
     * Get the nth data variable, or the default value if not set
     */
    public function getOr(int|string $index, mixed $default = null): mixed
    {
        return ($this->has($index)) ? $this->get($index) : $default;
    }

    /**
     * Set the nth data variable case
     */
    public function set(int|string $index, mixed $value): self
    {
        Arr::set($this->data, $index, $value);

        return $this;
    }

    /**
     * Merge the given array with the current data
     *
     * @param  array<mixed>  $data
     */
    public function merge(array $data): self
    {
        $this->data = array_replace($this->data, $data);

        return $this;
    }

    /**
     * Convert this to array (fetch all)
     *
     * @return array<mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Arrayable: Convert this to array (fetch all)
     *
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * Get the keys of the data repository
     *
     * @return array<int|string>
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * ArrayAccess: check if the given data (by index) exists.
     *
     * @param  int|string  $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * ArrayAccess: get the value of the given data by index.
     *
     * @param  int|string  $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->getOr($offset, default: null);
    }

    /**
     * ArrayAccess: set the value of the given data by index.
     *
     * @param  int|string  $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * ArrayAccess: unset the given data by index (sets value to null).
     *
     * @param  int|string  $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        Arr::forget($this->data, $offset);
    }

    /**
     * Iterator: Get the value for the current position.
     */
    public function current(): mixed
    {
        $key = $this->key();

        return ($key === null) ? null : $this->get($key);
    }

    /**
     * Iterator: Get the key for the current position
     *
     * @return int|string|null
     */
    public function key(): mixed
    {
        return $this->keys()[$this->iteratorPosition] ?? null;
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
        $key = $this->key();

        return ($key === null) ? false : $this->has($key);
    }
}
