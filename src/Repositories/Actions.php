<?php

namespace BradieTilley\Stories\Repositories;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Exceptions\StoryActionNotFoundException;

class Actions
{
    protected static array $all = [];

    /**
     * Store an Action so it can be fetched later
     */
    public static function store(string $identifier, Action $action): void
    {
        static::$all[$identifier] = $action;
    }

    /**
     * Fetch a previously stored Action by its identifier
     */
    public static function fetch(string $identifier): Action
    {
        return static::$all[$identifier] ?? throw StoryActionNotFoundException::make($identifier);
    }
}
