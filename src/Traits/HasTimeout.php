<?php

namespace BradieTilley\StoryBoard\Traits;

trait HasTimeout
{
    protected ?int $timeout = null; 
    protected ?bool $timeoutEnabled = null; 

    /**
     * Set a timeout for this story
     * 
     * @param int $timeout in seconds
     * @return $this 
     */
    public function timeout(int $timeout): self
    {
        $this->timeoutEnabled = true;
        $this->timeout = max(1, $timeout);

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