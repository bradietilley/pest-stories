<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story;

/**
 * @property ?string $name
 */
trait HasName
{
    /**
     * Set the name (or name fragment) of this story
     * 
     * @return $this
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name (or name fragment) of this story
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    public function getParentName(): ?string
    {
        return $this->parent ? $this->parent->getFullName() : null;
    }

    public function getFullName(): ?string
    {
        /** @var Story|self $this */
        $fullName = $this->getName();
        
        if ($this->parent) {
            $fullName = "{$this->getParentName()} {$fullName}";
        }

        /**
         * Only the most lowest level story should get prefixed with can or cannot
         */
        if (! $this->hasStories()) {
            if (property_exists($this, 'expectCan') && ($this->expectCan !== null)) {
                $can = $this->expectCan ? 'Can' : 'Cannot';

                $fullName = "[{$can}] {$fullName}";
            }
        }

        return $fullName;
    }
}
