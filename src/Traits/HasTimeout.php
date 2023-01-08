<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Testing\Timer\Timer;
use BradieTilley\StoryBoard\Testing\Timer\TimerUnit;
use BradieTilley\StoryBoard\Testing\Timer\TimerUpException;
use Closure;

trait HasTimeout
{
    protected ?int $timeout = null;

    protected ?bool $timeoutEnabled = null;

    private ?Timer $timer = null;

    /**
     * Set a timeout for this story.
     * Any value under 1 millisecond will set to 1 millisecond.
     *
     * @return $this
     */
    public function timeout(int|float $timeout, TimerUnit $unit = TimerUnit::SECOND): self
    {
        $this->timeoutEnabled = true;
        $this->timeout = $unit->toMicroseconds($timeout, $unit);

        return $this;
    }

    /**
     * Remove the timeout for this story
     *
     * @return $this
     */
    public function noTimeout(): self
    {
        $this->timeoutEnabled = false;
        $this->timeout = null;

        return $this;
    }

    /**
     * Inherit the timeout from its ancestors.
     */
    public function inheritTimeout(): void
    {
        /** @var HasInheritance|self $this */
        foreach ($this->getAncestors() as $level) {
            $enabled = $level->getProperty('timeoutEnabled');

            // If the child/parent has explicitly stated no timeout then return with no timeout
            if ($enabled === false) {
                return;
            }

            // If the child/parent has explicitly stated a timeout then set the timeout and return
            if ($enabled === true) {
                $this->timeout(
                    timeout: $level->getProperty('timeout'),
                    unit: TimerUnit::MICROSECOND,
                );

                return;
            }
        }
    }

    /**
     * Get the timeout (in microseconds)
     */
    public function getTimeoutMicroseconds(): int
    {
        return $this->timeout;
    }

    /**
     * Get the timer used for this story
     */
    public function getTimer(): ?Timer
    {
        return $this->timer;
    }

    /**
     * Create a timer for this story
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
}
