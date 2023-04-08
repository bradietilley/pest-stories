<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Alarm;
use BradieTilley\Stories\Assertion;
use BradieTilley\Stories\Callback;
use BradieTilley\Stories\Repeater;
use BradieTilley\Stories\Sequence;
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

/**
 * Create a new repeater
 */
function repeater(int $max = 1): Repeater
{
    return Repeater::make($max);
}

/**
 * Create a new alarm
 */
function alarm(int|float $amount, string $unit = Alarm::UNIT_MICROSECONDS): Alarm
{
    return Alarm::make($amount, $unit);
}

/**
 * Create a new sequence
 *
 * @param  iterable<Callback>  $callbacks
 */
function sequence(iterable $callbacks = []): Sequence
{
    return Sequence::make($callbacks);
}
