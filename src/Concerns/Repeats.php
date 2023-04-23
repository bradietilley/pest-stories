<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Concerns;

trait Repeats
{
    /**
     * Current iteration of the task
     */
    protected int $repeatsIndex = 0;

    /**
     * The number of times this task should repeat
     */
    protected int $repeatsTimes = 1;

    /**
     * Specify the number of times this task should repeat
     */
    public function repeat(int $times): static
    {
        $this->repeatsTimes = max(1, $times);

        return $this;
    }

    /**
     * Check if this task is still repeating (current iteration is less than maximum)
     */
    public function stillRepeating(): bool
    {
        return $this->repeatsIndex < $this->repeatsTimes;
    }

    /**
     * Increment the current iteration
     */
    public function incrementRepeatCounter(): void
    {
        $this->repeatsIndex++;
    }
}
