<?php

namespace BradieTilley\Stories\Exceptions;

use Exception;

class StoryActionInvalidException extends Exception
{
    public static function make(string $name): self
    {
        return new self(
            sprintf(
                'Story action `%s` is not a valid Action',
                $name,
            ),
        );
    }
}
