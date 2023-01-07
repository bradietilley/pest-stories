<?php

namespace BradieTilley\StoryBoard\Testing\Timer;

use BradieTilley\StoryBoard\Traits\HasCallbacks;
use BradieTilley\StoryBoard\Traits\RunOnce;
use Closure;
use Throwable;

class Timer
{
    use HasCallbacks;
    use RunOnce;

    private ?int $start = null;
    
    private ?int $end = null;
    
    private ?int $timeTaken = null;
    
    private ?int $timeRemaining = null;

    public function __construct(
        Closure $callback,
        ?Closure $timedout = null,
        ?Closure $finished = null,
        ?Closure $errored = null,
        ?Closure $after = null,
        private int $timeout = 60,
        private bool $rethrow = true,
        TimerUnit $unit = TimerUnit::SECOND,
    ) {
        // Convert the timeout (potentially seconds) to microseconds
        $this->timeout($timeout, $unit);

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
     * Specify the timeout before the callback should be aborted in one of several
     * units (seconds, milliseconds, microseconds)
     */
    public function timeout(int $timeout, TimerUnit $unit = TimerUnit::SECOND): self
    {
        $this->timeout = $unit->toMicroseconds($timeout);

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

    private function start(): void
    {
        $this->start = $this->getCurrentMicroseconds();
    }

    private function end(): void
    {
        $this->end = $this->getCurrentMicroseconds();

        $this->recordTime();
    }

    private function getCurrentMicroseconds(): int
    {
        return (int) (microtime(true) * 1000000);
    }

    /**
     * Run the timeout-bound callback
     */
    public function run()
    {
        if ($this->alreadyRun('run')) {
            return;
        }

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
            pcntl_alarm($this->getAlarmTimeout());
            
            // Run task that may take a while
            $this->start();
            $this->runCallback('callback');
            $this->end();

            // Stop timer
            pcntl_alarm(0);

            if ($this->getTimeRemaining() < 0) {
                throw new TimerUpException($this);
            }

            // Successful exit
            $args['exit'] = $result;
            
            // Run finished callback
            $response = $this->runCallback('finished', $args);
        } catch (TimerUpException $e) {
            $this->end();

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
            $this->end();

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
     * Get the amount of time (microseconds) specified as the maximum (timeout)
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Record the time taken and remaining
     */
    private function recordTime(): void
    {
        $this->timeTaken = (int) ($this->end - $this->start);
        $this->timeRemaining = (int) ($this->timeout - $this->timeTaken);
    }

    /**
     * Get the amount of time (microseconds) it had taken to complete the callback
     */
    public function getTimeTaken(): ?int
    {
        return $this->timeTaken;
    }

    /**
     * Get the amount of time left remaining (microseconds) after completing the callback
     */
    public function getTimeRemaining(): ?int
    {
        return $this->timeRemaining;
    }

    /**
     * Get the specified timeout as seconds (e.g. for pcntl alarm).
     * 
     * Always rounded up.
     */
    public function getAlarmTimeout(): int
    {
        return ceil(TimerUnit::MICROSECOND->toSeconds($this->timeout));
    }
}
