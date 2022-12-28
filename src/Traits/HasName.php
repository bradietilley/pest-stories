<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story;

/**
 * @property ?string $name
 */
trait HasName
{
    /**
     * Alias for setName()
     *
     * @return $this
     */
    public function name(string $name): self
    {
        return $this->setName($name);
    }

    /**
     * Set the name (or name fragment) of this story
     *
     * @return $this
     */
    public function setName(string $name): self
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

    /**
     * Get the parent test name
     *
     * Example: create something > as low level user > with correct permissions
     * Output:  create something as low level user
     */
    public function getParentName(): ?string
    {
        return $this->getParent() ? $this->getParent()->getFullName() : null;
    }

    /**
     * Get the full test name
     *
     * Example: create something > as low level user > with correct permissions
     * Output:  [Can] create something as low level user with correct permissions
     *
     * @requires HasInheritance, HasStories
     */
    public function getFullName(): ?string
    {
        /** @var HasInheritance|HasStories|HasName $this */
        $fullName = $this->getName();

        if ($this->hasParent()) {
            $fullName = "{$this->getParentName()} {$fullName}";
        }

        /**
         * Only the most lowest level story should get prefixed with can or cannot
         */
        if (! $this->hasStories()) {
            if (property_exists($this, 'can') && ($this->can !== null)) {
                $can = $this->can ? 'Can' : 'Cannot';

                $fullName = "[{$can}] {$fullName}";
            }
        }

        return $fullName;
    }
}
