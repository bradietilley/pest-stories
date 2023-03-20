<?php

namespace BradieTilley\Stories\Exceptions;

class FunctionAliasNotFoundException extends StoryException
{
    /**
     * Create an exception for when a function alias is created
     * but the given $function cannot be found
     */
    public static function make(string $original, string $function): self
    {
        return new self(
            sprintf(
                'Cannot use function `%s` as an alias for `%s`: Function not found',
                $function,
                $original,
            ),
        );
    }
}
