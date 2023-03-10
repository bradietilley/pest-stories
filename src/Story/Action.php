<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\ActionGeneratorNotFoundException;
use BradieTilley\StoryBoard\Exceptions\ActionNotFoundException;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;

class Action extends Runnable
{
    /**
     * Get the alias for this type of action (for use in config)
     */
    public static function getAliasName(): string
    {
        return 'action';
    }

    /**
     * Action not found
     */
    protected static function notFound(string $name): ActionNotFoundException
    {
        return StoryBoardException::actionNotFound($name);
    }

    /**
     * Generator not found
     */
    protected static function generatorNotFound(string $name): ActionGeneratorNotFoundException
    {
        return StoryBoardException::actionGeneratorNotFound($name);
    }
}
