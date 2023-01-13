<?php

namespace BradieTilley\StoryBoard;

use BradieTilley\StoryBoard\Story\Action;
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
