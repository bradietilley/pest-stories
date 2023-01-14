<?php

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
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

    public static function setAlias(string $alias, string $value): void
    {
        Settings::set(self::KEY.'aliases.'.$alias, $value);
    }

    /**
     * Get the class name from the config for the given alias
     * 
     * @return class-string
     */
    public static function getAliasClass(string $name): string
    {
        /** @var string $class */
        $class = Settings::get(self::KEY.'aliases.'.$name);

        if (empty($class)) {
            throw StoryBoardException::aliasNotFound($name);
        }

        if (! class_exists($class)) {
            throw StoryBoardException::aliasClassNotFound($name, $class);
        }

        return $class;
    }

    /**
     * Get the function name from the config for the given alias
     * 
     * @return string&callable
     */
    public static function getAliasFunction(string $name): string
    {
        /** @var string $function */
        $function = Settings::get(self::KEY.'aliases.'.$name);

        if (empty($function)) {
            throw StoryBoardException::aliasNotFound($name);
        }

        if (! function_exists($function)) {
            throw StoryBoardException::aliasFunctionNotFound($name, $function);
        }

        return $function;
    }
}
