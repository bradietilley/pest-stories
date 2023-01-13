<?php

namespace BradieTilley\StoryBoard\Story;

use Illuminate\Support\Facades\Config as Settings;

class Config
{
    public const KEY = 'storyboard.';

    public static function datasetsEnabled(): bool
    {
        return (bool) Settings::get(self::KEY.'datasets', false);
    }

    public static function enableDatasets(): void
    {
        Settings::set(self::KEY.'datasets', true);
    }

    public static function disableDatasets(): void
    {
        Settings::set(self::KEY.'datasets', false);
    }
}
