<?php

namespace BradieTilley\Stories;

use BradieTilley\Stories\Exceptions\StoryException;

class TestCaseUnavailableException extends StoryException
{
    public static function make(Story $story): self
    {
        return new self(
            sprintf(
                'The `PHPUnit\Framework\TestCase` instance was not available when the `%s` story was booted.',
                $story->getName(),
            )
        );
    }
}
