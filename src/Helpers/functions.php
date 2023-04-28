<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\PendingCalls\PendingActionCall;
use BradieTilley\Stories\Repositories\Dataset;
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
function action(string $name = null, Closure $callback = null, string $variable = null): Action|PendingActionCall
{
    if (($name !== null) && Action::isAction($name)) {
        return $name::make($name, $callback, $variable);
    }

    return Action::make($name, $callback, $variable);
}

/**
 * Get the active story's Dataset details
 *
 * @return Dataset|mixed
 */
function dataset(int $index = null, mixed $value = null): mixed
{
    $dataset = story()->dataset();

    if ($index !== null) {
        if ($value !== null) {
            $dataset->set($index, $value);

            return null;
        }

        return $dataset->get($index);
    }

    return $dataset;
}
