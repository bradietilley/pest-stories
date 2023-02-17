<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story\DebugContainer;

/**
 * This object (action) can be repeated several times.
 *
 * By providing a repeat count, the `repeating()` method will
 * return `true` *count* times.
 *
 * By providing no repeat, the repeating method will return `true`
 * once, and `false` thereafter.
 *
 * The dontRepeat is an alias for resetting the repeat (no repeat)
 */
trait HasRepeater
{
    /**
     * The counter to record how many times this has been run
     */
    protected int $repeatNum = 0;

    /**
     * The maxium times this can be run.
     */
    protected ?int $repeatMax = null;

    /**
     * Repeat the execution of this object several times
     *
     * Examples:
     *     2 = Runs twice
     *     1 = Runs once
     *     0 = Never runs
     */
    public function repeat(int $times): static
    {
        $this->repeatNum = 0;
        $this->repeatMax = max(0, $times);

        return $this;
    }

    /**
     * Do not repeat the execution of this object.
     *
     * i.e. Run once.
     */
    public function dontRepeat(): static
    {
        $this->repeatMax = null;

        return $this;
    }

    /**
     * Does this object repeat its execution?
     *
     * Examples:
     *      2 = true
     *      1 = true
     *      0 = true
     *      null = false
     */
    public function repeats(): bool
    {
        return $this->repeatMax !== null;
    }

    /**
     * Can this item be run? Whether repeating is allowed or not, this
     * should return true at least once, unless the number of repeats is 0
     *
     * while ($this->repeating()) {
     *     $this->doSomething();
     * }
     */
    public function repeating(): bool
    {
        $this->repeatNum++;

        // If this object isn't meant to repeat, allow true once, then false thereafter
        if ($this->repeats() === false) {
            $repeating = ($this->repeatNum === 1);
            
            DebugContainer::instance()->debug(
                sprintf(
                    'Repeater disabled: %s',
                    $repeating ? 'run this once' : 'not running',
                ),
            );

            return $repeating;
        }

        $repeating = $this->repeatNum <= $this->repeatMax;

        DebugContainer::instance()->debug(
            sprintf(
                'Repeater enabled (%d of %d): %s',
                $this->repeatNum,
                $this->repeatMax,
                ($repeating) ? 'repeating' : 'not repeating',
            ),
        );

        // Continue repeating until repeats counter is repeatMax
        return $repeating;
    }

    /**
     * Reset the repeater so that this object can be re-repeated, should
     * that ever be an option.
     */
    public function resetRepeater(): void
    {
        $this->repeatNum = 0;
    }
}
