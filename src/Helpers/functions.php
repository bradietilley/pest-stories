<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Assertion;
use BradieTilley\Stories\Story;
use Closure;

/**
 * Create a new story
 */
function story(string $name = '', Closure|callable|null $callback = null): Story
{
    return Story::make($name, $callback);
}

/**
 * Create a new assertion
 */
function assertion(string $name = '', Closure|callable|null $callback = null): Assertion
{
    return Assertion::make($name, $callback);
}

/**
 * Create a new action
 */
function action(string $name = '', Closure|callable|null $callback = null): Action
{
    return Action::make($name, $callback);
}
