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
     * Get the current iteration of the repeated task
     *
     * Before this task is run the first time, the value is 0
     * When this task gets run for the first time, this value is 1
     * This value will reach the maximum on its final repeat.
     */
    public function repeatsIndex(): int
    {
        return $this->repeatsIndex;
    }

    /**
     * Get the (maximum) number of times this task may run.
     *
     * By default, this will be 1 to indicate a single-run task.
     */
    public function repeatsTimes(): int
    {
        return $this->repeatsTimes;
    }

    /**
     * Check if this task is still repeating (current iteration is less than maximum)
     */
    public function repeats(): bool
    {
        return $this->repeatsIndex() < $this->repeatsTimes();
    }

    /**
     * Increment the current iteration
     */
    public function repeatsIncrement(): void
    {
        $this->repeatsIndex++;
    }
}
