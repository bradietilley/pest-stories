<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Testing\Timer\TimerUnit;

trait HasTimeout
{
    protected ?int $timeout = null;

    protected ?bool $timeoutEnabled = null; 

    /**
     * Set a timeout for this story.
     * Any value under 1 millisecond will set to 1 millisecond.
     * 
     * @return $this 
     */
    public function timeout(int $timeout, TimerUnit $unit = TimerUnit::SECOND): self
    {
        $this->timeoutEnabled = true;
        $this->timeout = $unit->toMicroseconds($timeout, $unit);

        return $this;
    }

    /**
     * Remove the timeout for this story
     * 
     * @return $this
     */
    public function noTimeout(): self
    {
        $this->timeoutEnabled = false;
        $this->timeout = null;

        return $this;
    }

    /**
     * Inherit the timeout from its ancestors.
     */
    public function inheritTimeout(): void
    {
        /** @var HasInheritance|self $this */

        foreach ($this->getAncestors() as $level) {
            $enabled = $level->getProperty('timeoutEnabled');

            // If the child/parent has explicitly stated no timeout then return with no timeout
            if ($enabled === false) {
                return;
            }

            // If the child/parent has explicitly stated a timeout then set the timeout and return
            if ($enabled === true) {
                $this->timeout($level->getProperty('timeout'));

                return;
            }
        }
    }
}