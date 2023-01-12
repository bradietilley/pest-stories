<?php

namespace BradieTilley\StoryBoard\Contracts;

use BradieTilley\StoryBoard\Testing\Timer\Timer;
use BradieTilley\StoryBoard\Testing\Timer\TimerUnit;
use BradieTilley\StoryBoard\Testing\Timer\TimerUpException;
use Closure;

interface WithTimeout
{
    /**
     * Set a timeout for this story.
     * Any value under 1 millisecond will set to 1 millisecond.
     */
    public function timeout(int|float $timeout, TimerUnit $unit = TimerUnit::SECOND): static;

    /**
     * Remove the timeout for this story
     */
    public function noTimeout(): static;

    /**
     * Inherit the timeout from its ancestors.
     */
    public function inheritTimeout(): void;

    /**
     * Get the timeout (in microseconds)
     */
    public function getTimeoutMicroseconds(): int;

    /**
     * Get the timer used for this story
     */
    public function getTimer(): ?Timer;

    /**
     * Create a timer for this story
     */
    public function createTimer(Closure $callback): Timer;
}
