<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Testing\Timer\Timer;
use BradieTilley\StoryBoard\Testing\Timer\TimerUnit;
use BradieTilley\StoryBoard\Testing\Timer\TimerUpException;
use Closure;

/**
 * This object's task (whatever that may be) can be limited
 * by a timeout. Exceeding this timeout will cause a timeout
 * failure that you can handle appropriate. For a Story, if
 * it exceeds the timeout, the TestCase is marked as failed.
 *
 * Under the hood it uses `Timer` which uses the `pcntl_*`
 * functions. For the moment, tests for this on Windows have
 * been skipped as the `pcntl_*` functions are not avaiable.
 *
 * On environments that have `pcntl_*` functions, the callback
 * will be aborted after the timeout is reached, but if on an
 * environment without `pcntl_*` function, the callback will
 * run from start-to-finish and simply fail at the end if the
 * timeout was exceeded.
 *
 * Supports inheritance: If a parent story has a timeout, it
 * will be shared to each of its children, whereby each child
 * gets the same timeout. E.g. if a parent has a timeout of 3
 * seconds and it has 5 children, each taking 1 second, then
 * the stories will pass as none exceed the 3 second timeout.
 *
 * @mixin \BradieTilley\StoryBoard\Contracts\WithInheritance
 */
trait HasTimeout
{
    /**
     * Number of microseconds to use as the timeout.
     */
    protected ?int $timeout = null;

    /**
     * Is the timeout enabled?
     * 
     * true = yes
     * false = no
     * null = inherit
     */
    protected ?bool $timeoutEnabled = null;

    /**
     * The Timer used to record and validate the time to run
     * this object/story/action/etc.
     */
    private ?Timer $timer = null;

    /**
     * Set a timeout for this object/story.
     * Any value under 1 microsecond will set to 1 microsecond.
     */
    public function timeout(int|float $timeout, TimerUnit $unit = TimerUnit::SECOND): static
    {
        $this->timeoutEnabled = true;
        $this->timeout = $unit->toMicroseconds($timeout);

        return $this;
    }

    /**
     * Remove the timeout for this object/story. The parent's timeout is not inherited.
     */
    public function noTimeout(): static
    {
        $this->timeoutEnabled = false;
        $this->timeout = null;

        return $this;
    }

    /**
     * Get the timeout (in microseconds)
     */
    public function getTimeoutMicroseconds(): int
    {
        return (int) $this->timeout;
    }

    /**
     * Get the timer used for this object/story
     */
    public function getTimer(): ?Timer
    {
        return $this->timer;
    }

    /**
     * Create a timer for this object/story
     */
    public function createTimer(Closure $callback): Timer
    {
        $timer = Timer::make($callback);

        $timer->rethrow();
        $timer->timeout($this->getTimeoutMicroseconds(), TimerUnit::MICROSECOND);
        $timer->timedout(fn (TimerUpException $e) => $this->runTearDown([
            'e' => $e,
            'exception' => $e,
        ]));

        return $timer;
    }

    /**
     * Inherit the timeout from its ancestors.
     */
    public function inheritTimeout(): void
    {
        foreach ($this->getAncestors() as $level) {
            /** @var ?bool $enabled */
            $enabled = $level->getProperty('timeoutEnabled');

            // If the child/parent has explicitly stated no timeout then return with no timeout
            if ($enabled === false) {
                return;
            }

            // If the child/parent has explicitly stated a timeout then set the timeout and return
            if ($enabled === true) {
                /** @var int|float|null $timeout */
                $timeout = $level->getProperty('timeout');

                $this->timeout(
                    timeout: $timeout ?? 0,
                    unit: TimerUnit::MICROSECOND,
                );

                return;
            }
        }
    }
}
