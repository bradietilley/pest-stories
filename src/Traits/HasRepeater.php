<?php

namespace BradieTilley\StoryBoard\Traits;

trait HasRepeater
{
    protected int $repeatNum = 0;

    protected ?int $repeatMax = null;

    /**
     * Repeat the execution of this object several times
     */
    public function repeat(int $times): static
    {
        $this->repeatNum = 0;
        $this->repeatMax = max(0, $times);

        return $this;
    }

    /**
     * Do not repeat the execution of this object
     */
    public function dontRepeat(): static
    {
        $this->repeatMax = null;

        return $this;
    }

    /**
     * Does this object repeat its execution?
     */
    public function repeats(): bool
    {
        return $this->repeatMax !== null;
    }

    /**
     * Can this item be run? Whether repeating is allowed or not, this
     * should return true at least once, unless the number of repeats is 0
     *
     * while ($this->repeating()) {
     *     $this->doSomething();
     * }
     */
    public function repeating(): bool
    {
        $this->repeatNum++;

        // If this object isn't meant to repeat, allow true once, then false thereafter
        if ($this->repeats() === false) {
            return $this->repeatNum === 1;
        }

        // Continue repeating until repeats counter is repeatMax
        return $this->repeatNum <= $this->repeatMax;
    }

    public function resetRepeater(): void
    {
        $this->repeatNum = 0;
    }
}
