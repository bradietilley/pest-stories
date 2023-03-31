<?php

namespace BradieTilley\Stories\Exceptions;

class InvocationOrphanedPropertyException extends StoryException
{
    public static function make(string $property): self
    {
        return new self(
            sprintf(
                'Invocation failed: property `%s` is orphaned and does not have a parent object.',
                $property,
            ),
        );
    }
}
