<?php

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use Illuminate\Support\Facades\Config as Settings;

class Config
{
    public const KEY = 'storyboard.';

    /**
     * Get a given storyboard configuration value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Settings::get(sprintf('%s%s', self::KEY, $key), $default);
    }

    /**
     * Set a given storyboard configuration value
     */
    public static function set(string $key, mixed $value): void
    {
        Settings::set(sprintf('%s%s', self::KEY, $key), $value);
    }

    /**
     * Alias of Config::get() for `datasets` config
     */
    public static function datasetsEnabled(): bool
    {
        return (bool) self::get('datasets', false);
    }

    /**
     * Alias of Config::set() for `datasets` config
     */
    public static function enableDatasets(): void
    {
        self::set('datasets', true);
    }

    /**
     * Alias of Config::set() for `datasets` config
     */
    public static function disableDatasets(): void
    {
        self::set('datasets', false);
    }

    /**
     * Set a replacement function or class for the given alias
     */
    public static function setAlias(string $alias, string $value): void
    {
        self::set('aliases.'.$alias, $value);
    }

    /**
     * Get the class name from the config for the given alias
     *
     * @return class-string
     */
    public static function getAliasClass(string $name, string $subclass): string
    {
        /** @var string $class */
        $class = self::get('aliases.'.$name);

        if (empty($class)) {
            throw StoryBoardException::aliasNotFound($name);
        }

        if (! class_exists($class)) {
            throw StoryBoardException::aliasClassNotFound($name, $class);
        }

        if ($class === $subclass) {
            return $class;
        }

        if (! is_subclass_of($class, $subclass)) {
            throw StoryBoardException::aliasClassNotValid($name, $class, $subclass);
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
        $function = self::get('aliases.'.$name);

        if (empty($function)) {
            throw StoryBoardException::aliasNotFound($name);
        }

        if (! function_exists($function)) {
            throw StoryBoardException::aliasFunctionNotFound($name, $function);
        }

        return $function;
    }
}
