<?php

namespace BradieTilley\Stories\Exceptions;

class InvocationOrphanedMethodException extends StoryException
{
    public static function make(string $method): self
    {
        return new self(
            sprintf(
                'Invocation failed: method `%s` is orphaned and does not have a parent object.',
                $method,
            ),
        );
    }
}
