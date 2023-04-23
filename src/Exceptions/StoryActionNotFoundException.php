<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Exceptions;

use Exception;

class StoryActionNotFoundException extends Exception
{
    public static function make(string $name): self
    {
        return new self(
            sprintf(
                'Story action `%s` could not be found',
                $name,
            ),
        );
    }
}
