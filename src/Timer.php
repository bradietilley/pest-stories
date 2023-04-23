<?php

namespace BradieTilley\Stories;

use BradieTilley\Stories\Exceptions\TimerException;

class Timer
{
    public ?float $timeout = null;

    public ?float $start = null;

    public ?float $end = null;

    public bool $abort = false;

    /**
     * Determine if pcntl signals/alarms are supported and that
     * this timer is set to abort once the timeout is reached and
     * that this timer has a timeout specified
     */
    private function enabledWithAbort(): bool
    {
        return function_exists('pcntl_signal')
            && function_exists('pcntl_alarm')
            && ($this->abort === true)
            && ($this->timeout !== null);
    }

    /**
     * Begin timing.
     *
     * If pcntl aborting is supported and enabled, the timer
     * will throw an exception at the next whole second. For
     * example a timeout of 1.1 seconds will force shut down
     * the task's process after 2 whole seconds due to pcntl
     * only supporting whole second alarms.
     */
    public function start(): static
    {
        $this->start = microtime(true);

        if ($this->enabledWithAbort()) {
            /** @var float $timeout */
            $timeout = $this->timeout;

            pcntl_signal(SIGALRM, function () {
                $this->throw();
            });

            pcntl_alarm((int) ceil($timeout));
        }

        return $this;
    }

    /**
     * Stop timing.
     *
     * If pcntl aborting is supported and enabled, the timer
     * will no longer throw the exception as the task is now
     * considered safe and finished.
     */
    public function end(): static
    {
        $this->end = microtime(true);

        if ($this->enabledWithAbort()) {
            pcntl_alarm(0);
        }

        return $this;
    }

    /**
     * Get the time taken in seconds
     */
    public function timeTaken(): float
    {
        return ($this->end ?? 0) - ($this->start ?? 0);
    }

    /**
     * Enable aborting (if supported) once the time limit
     * is reached
     */
    public function abort(): static
    {
        $this->abort = true;

        return $this;
    }

    /**
     * Specify the timeout in seconds
     */
    public function timeout(float $seconds): static
    {
        $this->timeout = $seconds;

        return $this;
    }

    /**
     * Throw a timer exception for this timer.
     *
     * @throws TimerException
     */
    public function throw(): never
    {
        throw TimerException::make($this);
    }

    /**
     * Check if the time taken so far has exceeded the specified
     * timeout and if it has throw a Timer Exception.
     *
     * @throws TimerException
     */
    public function check(): void
    {
        if ($this->timeTaken() > $this->timeout) {
            $this->throw();
        }
    }

    /**
     * Does this timer have a timeout specified?
     */
    public function hasTimeout(): bool
    {
        return $this->timeout !== null;
    }
}
