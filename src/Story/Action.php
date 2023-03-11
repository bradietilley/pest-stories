<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Story;

class Action extends Runnable
{
    /**
     * Get the alias for this type of action (for use in config)
     */
    public static function getAliasName(): string
    {
        return 'action';
    }
}
