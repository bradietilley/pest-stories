<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Concerns;

use BradieTilley\Stories\Timer;
use InvalidArgumentException;

trait Times
{
    protected ?Timer $timer = null;

    /**
     * Get and or create the Timer for this task
     */
    public function timer(): Timer
    {
        return $this->timer ??= new Timer();
    }

    /**
     * Specify the timeout in seconds
     */
    public function mustRunWithinSeconds(float $seconds): static
    {
        $this->timer()->timeout($seconds);

        return $this;
    }

    /**
     * Specify the timeout in milliseconds
     */
    public function mustRunWithinMilliseconds(float $milliseconds): static
    {
        $this->timer()->timeout($milliseconds / 1000);

        return $this;
    }

    /**
     * Specify the timeout and unit of measurement for time
     */
    public function timeout(float $timeout, string $unit = 's'): static
    {
        return match ($unit) {
            'ms' => $this->mustRunWithinMilliseconds($timeout),
            's' => $this->mustRunWithinSeconds($timeout),
            default => throw new InvalidArgumentException('Unknown unit: '.$unit),
        };
    }

    /**
     * Abort after the timeout is reached using pcntl signal/alarm
     */
    public function abortAfterTimeout(): static
    {
        $this->timer()->abort();

        return $this;
    }

    /**
     * Determine if a timeout has been specified for this task
     */
    public function hasTimer(): bool
    {
        return $this->timer()->hasTimeout();
    }
}
