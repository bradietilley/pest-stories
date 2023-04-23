<?php

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Story;
use Closure;

/**
 * Create an anonymous story
 */
function story(): Story
{
    return new Story();
}

/**
 * Create an action
 */
function action(string $name = null, Closure $callback = null, string $variable = null): Action
{
    return new Action($name, $callback, $variable);
}
