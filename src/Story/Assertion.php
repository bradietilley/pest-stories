<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\AssertionGeneratorNotFoundException;
use BradieTilley\StoryBoard\Exceptions\AssertionNotFoundException;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;

class Assertion extends Runnable
{
    /**
     * Get the alias for this type of assertion (for use in config)
     */
    public static function getAliasName(): string
    {
        return 'assertion';
    }

    /**
     * Assertion not found
     */
    protected static function notFound(string $name): AssertionNotFoundException
    {
        return StoryBoardException::assertionNotFound($name);
    }

    /**
     * Generator not found
     */
    protected static function generatorNotFound(string $name): AssertionGeneratorNotFoundException
    {
        return StoryBoardException::assertionGeneratorNotFound($name);
    }
}
