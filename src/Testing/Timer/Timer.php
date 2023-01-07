<?php

namespace BradieTilley\StoryBoard\Testing\Timer;

use BradieTilley\StoryBoard\Traits\HasCallbacks;
use Closure;
use Throwable;

class Timer
{
    use HasCallbacks;

    private bool $ran = false;
    
    private ?int $timeTaken = null;

    public function __construct(
        Closure $callback,
        ?Closure $timedout = null,
        ?Closure $finished = null,
        ?Closure $errored = null,
        ?Closure $after = null,
        private int $timeout = 60,
        private bool $rethrow = true,
    ) {
        $this->setCallback('callback', $callback);
        $this->setCallback('timedout', $timedout);
        $this->setCallback('finished', $finished);
        $this->setCallback('errored', $errored);
        $this->setCallback('after', $after);
    }

    public static function make(
        Closure $callback,
        ?Closure $timedout = null,
        ?Closure $finished = null,
        ?Closure $errored = null,
        ?Closure $after = null,
        int $timeout = 60,
        bool $rethrow = true,
    ): self
    {
        return new self(...func_get_args());
    }

    /**
     * Specify the timeout before the callback should be aborted.
     */
    public function timeout(int $timeout): self
    {
        $this->timeout = max(1, $timeout);

        return $this;
    }

    /**
     * Register callback to run when finished
     *
     * Closure arguments:
     *
     *      - int $seconds (remaining)
     */
    public function finished(?Closure $finished): self
    {
        return $this->setCallback('finished', $finished);
    }

    /**
     * Register callback to run when unexpected exception thrown
     *
     * Closure arguments:
     *
     *      - Throwable $e|exception
     */
    public function errored(?Closure $errored): self
    {
        return $this->setCallback('errored', $errored);
    }

    /**
     * Register callback to run when the task passes, fails or is timedout
     */
    public function after(?Closure $after): self
    {
        return $this->setCallback('after', $after);
    }

    /**
     * Register callback to run when time out reached
     *
     * Closure arguments:
     *
     *      - int $seconds (time taken)
     */
    public function timedout(?Closure $timedout): self
    {
        return $this->setCallback('timedout', $timedout);
    }

    /**
     * Rethrow unexpected exceptions that are piped into the optional
     * errored/timedout callbacks, ensuring that exceptions are always
     * thrown
     */
    public function rethrow(): self
    {
        $this->rethrow = true;

        return $this;
    }

    /**
     * Do not rethrow exceptions
     */
    public function dontRethrow(): self
    {
        $this->rethrow = false;

        return $this;
    }

    /**
     * Run the timeout-bound callback
     */
    public function run()
    {
        if ($this->ran) {
            return;
        }

        $this->ran = true;

        pcntl_signal(SIGALRM, function ($signal) {
            throw new TimerUpException($this);
        });

        $args = [
            'timer' => $this,
            'timeout' => $this->timeout,
            'seconds' => $this->timeout,
            'e' => null,
            'exception' => null,
        ];

        $result = TimerResult::PASSED;
        $e = null;
        $response = null;

        try {
            // Start timer
            pcntl_alarm($this->timeout);

            // Run task that may take a while
            $this->runCallback('callback');

            // Stop timer (get seconds remaining)
            $remain = pcntl_alarm(0);
            // Record the time taken to execute
            $this->timeTaken = $this->timeout - $remain;

            // Temp BC
            $args['seconds'] = $remain;

            // Successful exit
            $args['exit'] = $result;
            
            // Run finished callback
            $response = $this->runCallback('finished', $args);
        } catch (TimerUpException $e) {
            // Record the time taken to execute
            $this->timeTaken = $this->timeout;

            $result = TimerResult::TIMED_OUT;

            $args['exit'] = $result;
            $args['e'] = $e;
            $args['exception'] = $e;

            try {
                // Run finished callback
                $this->runCallback('timedout', $args);
            } catch (TimerUpException $e) {
                // Rethrow the timerup exception if we intentionally throw it in the timedout callback
                $this->rethrow();
            }
        } catch (Throwable $e) {
            $result = TimerResult::FAILED;
            $args['exit'] = $result;

            // Stop timer (get seconds remaining)
            $remain = pcntl_alarm(0);
            // Record the time taken to execute
            $this->timeTaken = $this->timeout - $remain;

            $this->runCallback('errored', $args);
        }

        $this->runCallback('after', $args);

        if ($this->rethrow && $e) {
            throw $e;
        }

        return $response;
    }

    public function __destruct()
    {
        $this->run();
    }

    /**
     * Get the amount of time specified as the maximum (timeout)
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Get the amount of time it had taken to complete the callback
     */
    public function getTimeTaken(): ?int
    {
        return $this->timeTaken;
    }

    /**
     * Get the amount of time left remaining after completing the callback
     */
    public function getTimeRemaining(): ?int
    {
        return $this->getTimeout() - $this->getTimeTaken();
    }
}
