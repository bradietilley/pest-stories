<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Alarm;
use BradieTilley\Stories\Assertion;
use BradieTilley\Stories\Exceptions\ClassAliasNotFoundException;
use BradieTilley\Stories\Exceptions\ClassAliasNotSubClassException;
use BradieTilley\Stories\Exceptions\FunctionAliasNotFoundException;
use BradieTilley\Stories\Repeater;
use BradieTilley\Stories\Story;

class StoryAliases
{
    protected static array $classes = [
        Story::class => Story::class,
        Action::class => Action::class,
        Assertion::class => Assertion::class,
        Repeater::class => Repeater::class,
        Alarm::class => Alarm::class,
    ];

    protected static array $functions = [
        'test' => 'test',
    ];

    /**
     * @return class-string
     */
    public static function getClassAlias(string $original): string
    {
        return static::$classes[$original];
    }

    /**
     * Set the class to use for the given type, expecting the given class to exist
     * and be a subclass of the provided parent
     */
    public static function setClassAlias(string $original, string $class): void
    {
        if (! class_exists($class)) {
            throw ClassAliasNotFoundException::make($original, $class);
        }

        /** @phpstan-ignore-next-line */
        if (! (is_subclass_of($class, $original) || ($class === $original))) {
            throw ClassAliasNotSubClassException::make($original, $class);
        }

        static::$classes[$original] = $class;
    }

    /**
     * Get the function to use when registering a Story's Pest test
     */
    public static function getFunction(string $original): string
    {
        return static::$functions[$original];
    }

    /**
     * Set the function to use when registering a Story's Pest test
     */
    public static function setFunction(string $original, string $function): void
    {
        if (! function_exists($function)) {
            throw FunctionAliasNotFoundException::make($original, $function);
        }

        static::$functions[$original] = $function;
    }
}
