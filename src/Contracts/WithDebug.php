<?php

namespace BradieTilley\StoryBoard\Contracts;

use BradieTilley\StoryBoard\Story\DebugContainer;

/**
 * This object has ability to record debug against the object,
 * and toggle debug on and off
 */
interface WithDebug
{
    /**
     * Enable dumping of debug on failure
     */
    public function debug(): static;

    /**
     * Is debug enabled for this object?
     */
    public function debugEnabled(): bool;

    /**
     * Get this story's debug container
     */
    public function getDebugContainer(): DebugContainer;

    /**
     * Assign the debug container that's used in global functions
     * like `debug()` and `error()` to be this story's debug
     * container.
     */
    public function assignDebugContainer(): static;

    /**
     * Print out the debug container
     */
    public function printDebug(): void;
}
