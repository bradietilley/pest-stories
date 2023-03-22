<?php

namespace BradieTilley\Stories\Exceptions;

use BradieTilley\Stories\Alarm;

class AlarmException extends StoryException
{
    public static function make(Alarm $alarm): self
    {
        return new self(
            sprintf(
                'Failed to run within the specified %s seconds limit',
                $alarm->seconds(),
            )
        );
    }
}
