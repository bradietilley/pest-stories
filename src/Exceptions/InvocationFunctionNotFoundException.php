<?php

namespace BradieTilley\Stories\Exceptions;

class InvocationFunctionNotFoundException extends StoryException
{
    public static function make(string $function): self
    {
        return new self(
            sprintf(
                'Invocation failed: function `%s` not found.',
                $function,
            ),
        );
    }
}
