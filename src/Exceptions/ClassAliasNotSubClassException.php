<?php

namespace BradieTilley\Stories\Exceptions;

class ClassAliasNotSubClassException extends StoryException
{
    /**
     * Create an exception for when a class alias is created
     * but the given $class is nto a subclass of the $original
     */
    public static function make(string $original, string $class): self
    {
        return new self(
            sprintf(
                'Cannot use class `%s` as an alias for `%s`: Class is not a valid subclass',
                $class,
                $original,
            ),
        );
    }
}
