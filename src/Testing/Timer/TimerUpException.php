<?php

namespace BradieTilley\StoryBoard\Testing\Timer;

use Exception;

class TimerUpException extends Exception
{
    public function __construct(private Timer $timer)
    {
    }

    /**
     * Get the instance of the Timer that failed
     */
    public function getTimer(): Timer
    {
        return $this->timer;
    }

    /**
     * Get the amount of time specified as the maximum (timeout)
     */
    public function getTimeout(): int
    {
        return $this->timer->getTimeout();
    }

    /**
     * Get the amount of time it had taken to complete the callback
     */
    public function getTimeTaken(): int
    {
        return (int) $this->timer->getTimeTaken();
    }

    /**
     * Get the amount of time specified as the maximum, formatted as "x seconds"
     */
    public function getTimeoutFormatted(): string
    {
        return $this->getSecondsFormatted(
            value: $this->getTimeout(),
        );
    }

    /**
     * Format a given number of seconds in nice readable form
     */
    private function getSecondsFormatted(int $value): string
    {
        return TimerUnit::SECOND->format(
            TimerUnit::MICROSECOND->toSeconds($value),
        );
    }
}
