<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Story;
use Closure;

/**
 * Fetch the current story instance or create an anonymous story
 */
function story(): Story
{
    return Story::getInstance() ?? new Story();
}

/**
 * Create an action
 */
function action(string $name = null, Closure $callback = null, string $variable = null): Action
{
    return Action::make($name, $callback, $variable);
}
