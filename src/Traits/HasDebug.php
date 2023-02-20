<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Traits;

use function BradieTilley\StoryBoard\debug;
use BradieTilley\StoryBoard\Story\Config;
use BradieTilley\StoryBoard\Story\DebugContainer;

/**
 * This object has ability to record debug against the object,
 * and toggle debug on and off
 *
 * *
 * @mixin \BradieTilley\StoryBoard\Contracts\WithDebug
 */
trait HasDebug
{
    protected ?string $debugLevel = null;

    protected DebugContainer $debug;

    /**
     * Enable dumping of debug on failure
     */
    public function debug(string $level = 'debug'): static
    {
        $this->debugLevel = $level;

        return $this;
    }

    /**
     * Get this story's debug container
     */
    public function getDebugContainer(): DebugContainer
    {
        return $this->debug;
    }

    /**
     * Assign the debug container that's used in global functions
     * like `debug()` and `error()` to be this story's debug
     * container.
     */
    public function assignDebugContainer(): static
    {
        DebugContainer::swap($this->getDebugContainer());

        return $this;
    }

    /**
     * Is debug enabled for this object?
     */
    public function debugEnabled(): bool
    {
        return $this->debugLevel || Config::debugEnabled();
    }

    /**
     * Dump the debug to the terminal or wherever is configured.
     */
    public function printDebug(): void
    {
        $storyLevel = $this->debugLevel ?? 'debug';
        $configLevel = Config::getString('debug.level', 'debug');

        $actual = DebugContainer::levelHierarchy($storyLevel);
        $expect = DebugContainer::levelHierarchy($configLevel);
        $level = ($actual >= $expect) ? $storyLevel : $configLevel;

        $this->getDebugContainer()->printDebug($level);
    }
}
