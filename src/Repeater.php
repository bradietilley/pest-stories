<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Helpers\StoryAliases;

/**
 * @method static static make(int $max = 1)
 */
class Repeater
{
    protected int $run = 0;

    public function __construct(protected int $max = 1)
    {
    }

    public static function make(): static
    {
        $class = StoryAliases::getClassAlias(Repeater::class);

        /** @var static $repeater */
        $repeater = new $class(...func_get_args());

        return $repeater;
    }

    /**
     * Get the maximum amount of repeats
     */
    public function max(): int
    {
        return $this->max;
    }

    /**
     * Get the run index (First = 1; Last = Max)
     */
    public function run(): int
    {
        return $this->run;
    }

    /**
     * Set the maximum number of times the repeater may run
     */
    public function setMax(int $max): static
    {
        $this->max = $max;

        return $this;
    }

    /**
     * Record this as being run once more
     */
    public function increment(): static
    {
        $this->run++;

        return $this;
    }

    /**
     * Can this be run more?
     */
    public function more(): bool
    {
        return $this->run < $this->max;
    }

    /**
     * Reset the repeater
     */
    public function reset(): static
    {
        $this->run = 0;

        return $this;
    }

    /**
     * Stop the repeater from runninga another iteration
     */
    public function stop(): static
    {
        $this->run = $this->max;

        return $this;
    }
}
