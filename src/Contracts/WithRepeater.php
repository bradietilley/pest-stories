<?php

namespace BradieTilley\StoryBoard\Contracts;

interface WithRepeater
{
    /**
     * Repeat the execution of this object several times
     */
    public function repeat(int $times): static;

    /**
     * Do not repeat the execution of this object
     */
    public function dontRepeat(): static;

    /**
     * Does this object repeat its execution?
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
     * Reset the repeater
     */
    public function resetRepeater(): void;
}
