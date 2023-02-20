<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard;

use BradieTilley\StoryBoard\Story\Action;
use BradieTilley\StoryBoard\Story\DebugContainer;
use BradieTilley\StoryBoard\Story\Tag;
use Closure;

/**
 * Create a new story. Alias of:
 *
 *    Story::make();
 */
function story(string $message = null): Story
{
    return Story::make($message);
}

/**
 * Create a new action. Alias of:
 *
 *    Action::make();
 */
function action(string $name, ?Closure $generator = null, ?string $variable = null, ?int $order = null): Action
{
    return Action::make($name, $generator, $variable, $order);
}

/**
 * Create a new tag. Alias of:
 *
 *    Tag::make();
 */
function tag(string $name, Closure|string|int|float|bool|null $value = null, ?int $order = null): Tag
{
    return Tag::make($name, $value, $order);
}

/**
 * Record a debug-level entry in the debug container. Alias of:
 *
 *     DebugContainer::instance()->debug();
 */
function debug(mixed ...$arguments): DebugContainer
{
    return DebugContainer::instance()->debug(...$arguments);
}

/**
 * Record an info-level entry in the debug container. Alias of:
 *
 *     DebugContainer::instance()->info();
 */
function info(mixed ...$arguments): DebugContainer
{
    return DebugContainer::instance()->info(...$arguments);
}

/**
 * Record a warning-level entry in the debug container. Alias of:
 *
 *     DebugContainer::instance()->warning();
 */
function warning(mixed ...$arguments): DebugContainer
{
    return DebugContainer::instance()->warning(...$arguments);
}

/**
 * Record an error-level entry in the debug container. Alias of:
 *
 *     DebugContainer::instance()->error();
 */
function error(mixed ...$arguments): DebugContainer
{
    return DebugContainer::instance()->error(...$arguments);
}
