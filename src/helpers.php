<?php

namespace BradieTilley\StoryBoard;

use BradieTilley\StoryBoard\Story\Action;
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
