<?php

namespace BradieTilley\StoryBoard\Story;

use Illuminate\Support\Facades\Config as Settings;

class Config
{
    public const KEY = 'storyboard.';

    /**
     * Alias of Config::get() for `datasets` config
     */
    public static function datasetsEnabled(): bool
    {
        return (bool) Settings::get(self::KEY.'datasets', false);
    }

    /**
     * Alias of Config::set() for `datasets` config
     */
    public static function enableDatasets(): void
    {
        Settings::set(self::KEY.'datasets', true);
    }

    /**
     * Alias of Config::set() for `datasets` config
     */
    public static function disableDatasets(): void
    {
        Settings::set(self::KEY.'datasets', false);
    }
}
