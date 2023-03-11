<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Story;

class Assertion extends Runnable
{
    /**
     * Get the alias for this type of assertion (for use in config)
     */
    public static function getAliasName(): string
    {
        return 'assertion';
    }
}
