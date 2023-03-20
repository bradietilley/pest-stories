<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Callback;
use BradieTilley\Stories\Exceptions\CallbackFetchNotFoundException;

class CallbackRepository
{
    /** @var array<string,array<string,callback>> Callback repository */
    protected static array $store = [];

    /**
     * Persist a callback of the given type (action, assertion, etc) to
     * the repository
     */
    public static function store(string $type, Callback $callback): void
    {
        static::$store[$type] ??= [];
        static::$store[$type][$callback->getName()] = $callback;
    }

    /**
     * Fetch a callback of the given type (action, assertion, etc) from
     * the repository
     *
     * @throws CallbackFetchNotFoundException
     */
    public static function fetch(string $type, string $name): Callback
    {
        if (empty(static::$store[$type][$name])) {
            throw CallbackFetchNotFoundException::make($type, $name);
        }

        return static::$store[$type][$name];
    }

    /**
     * Flush all stored callbacks from the repository
     */
    public static function flush(): void
    {
        static::$store = [];
    }
}
