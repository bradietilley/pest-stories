<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Contracts;

/**
 * This object (action) can be repeated several times.
 *
 * By providing a repeat count, the `repeating()` method will
 * return `true` *count* times.
 *
 * By providing no repeat, the repeating method will return `true`
 * once, and `false` thereafter.
 *
 * The dontRepeat is an alias for resetting the repeat (no repeat)
 */
interface WithRepeater
{
    /**
     * Repeat the execution of this object several times
     *
     * Examples:
     *     2 = Runs twice
     *     1 = Runs once
     *     0 = Never runs
     */
    public function repeat(int $times): static;

    /**
     * Do not repeat the execution of this object.
     *
     * i.e. Run once.
     */
    public function dontRepeat(): static;

    /**
     * Does this object repeat its execution?
     *
     * Examples:
     *      2 = true
     *      1 = true
     *      0 = true
     *      null = false
     */
    public function repeats(): bool;

    /**
     * Can this item be run? Whether repeating is allowed or not, this
     * should return true at least once, unless the number of repeats is 0
     *
     * while ($this->repeating()) {
     *     $this->doSomething();
     * }
     */
    public function repeating(): bool;

    /**
     * Reset the repeater so that this object can be re-repeated, should
     * that ever be an option.
     */
    public function resetRepeater(): void;
}
