<?php

namespace BradieTilley\Stories\Exceptions;

class CallbackFetchNotFoundException extends StoryException
{
    /**
     * Create an exception when a callback class such as an action,
     * assertion or story is fetched by name, but is ultimately not
     * found
     */
    public static function make(string $type, string $name): self
    {
        return new self(
            sprintf(
                'Cannot find the %s callback with name `%s`',
                $type,
                $name,
            ),
        );
    }
}
