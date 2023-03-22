<?php

namespace BradieTilley\Stories\Exceptions;

class ClassAliasNotFoundException extends StoryException
{
    /**
     * Create an exception for when a class alias is created
     * but the given $class cannot be found
     */
    public static function make(string $original, string $class): self
    {
        return new self(
            sprintf(
                'Cannot use class `%s` as an alias for `%s`: Class not found',
                $class,
                $original,
            ),
        );
    }
}
