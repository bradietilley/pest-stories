<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;

/**
 * @property ?string $name
 */
trait HasName
{
    protected array $fullName = [];

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
        $parent = $this->getParent();

        if ($parent === null) {
            return null;
        }

        if ((StoryBoard::datasetsEnabled() === true) && ($parent instanceof StoryBoard)) {
            return null;
        }

        return $parent->getFullName();
    }

    /**
     * Get the full test name
     *
     * Example: create something > as low level user > with correct permissions
     * Output:  [Can] create something as low level user with correct permissions
     *
     * @requires Story
     */
    public function getFullName(): ?string
    {
        $key = StoryBoard::datasetsEnabled() ? 'dataset' : 'full';

        if (isset($this->fullName[$key])) {
            return $this->fullName[$key];
        }

        /** @var Story $this */
        $this->register();
        
        // Start with this test's name
        $fullName = $this->getName();

        /**
         * Append names from scenarios (where scenarios opt to `->appendName()`)
         */
        if (method_exists($this, 'getNameFromScenarios')) {
            $appendName = $this->getNameFromScenarios();

            if ($appendName !== null) {
                $fullName = trim("{$fullName} {$appendName}");
            }
        }

        /**
         * Prepend the parent story name
         */
        if ($this->hasParent()) {
            $fullName = trim("{$this->getParentName()} {$fullName}");
        }

        /**
         * Only the most lowest level story should get prefixed with can or cannot
         */
        if (! $this->hasStories()) {
            if (property_exists($this, 'can')) {
                if ($this->can === null) {
                    $this->can = $this->inheritFromParents('getCan');
                }
                
                if ($this->can !== null) {
                    $can = $this->can ? 'Can' : 'Cannot';

                    $fullName = "[{$can}] {$fullName}";
                }
            }
        }

        return $this->fullName[$key] = $fullName;
    }
}
