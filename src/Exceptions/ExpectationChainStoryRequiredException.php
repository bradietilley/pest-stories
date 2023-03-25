<?php

namespace BradieTilley\Stories\Exceptions;

class ExpectationChainStoryRequiredException extends StoryException
{
    /**
     * Create an exception for when you call ->story() on an expectation
     * chain that doesn't have a bound story
     */
    public static function make(): self
    {
        return new self(
            'The expectation chain must be bound to a story in order to convert it back to a story',
        );
    }
}
