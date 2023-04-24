<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Exceptions;

use BradieTilley\Stories\Timer;
use Exception;

class TimerException extends Exception
{
    public function __construct(public Timer $timer)
    {
        parent::__construct(
            sprintf(
                'Failed to run the task within the specified timeframe of %s seconds (took %s seconds)',
                $timer->timeout ?? 'N/A',
                $timer->timeTaken(),
            ),
        );
    }

    public static function make(Timer $timer): self
    {
        return new self($timer);
    }
}
