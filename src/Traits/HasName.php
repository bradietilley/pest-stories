<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Str;

/**
 * @property ?string $name
 */
trait HasName
{
    protected array $fullName = [];

    public function __cloneName(): void
    {
        $this->name = Str::random(32);
    }

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

    public function inheritName(): void
    {
        $datasetKey = StoryBoard::datasetsEnabled() ? 'dataset' : 'default';

        if (isset($this->fullName[$datasetKey])) {
            return;
        }

        $name = [];
        $levels = array_reverse($this->getAncestors());
        $first = array_key_first($levels);

        foreach ($levels as $key => $level) {
            if ($key === $first) {
                if (StoryBoard::datasetsEnabled()) {
                    continue;
                }
            }

            $name[] = $level->getLevelName();
        }

        $name = trim(preg_replace('/\s+/', ' ', implode(' ', $name)));

        $this->fullName[$datasetKey] = $name;
    }

    public function getFullName(): string
    {
        $datasetKey = StoryBoard::datasetsEnabled() ? 'dataset' : 'default';

        return $this->fullName[$datasetKey] ?? $this->getName();
    }

    public function getLevelName(): string
    {
        $name = $this->getName();

        /**
         * Append names from actions (where actions opt to `->appendName()`)
         */
        if (method_exists($this, 'getNameFromActions')) {
            $name = "{$name} {$this->getNameFromActions()}";
        }

        $name = trim($name);

        return $name;
    }

    // /**
    //  * Get the full test name
    //  *
    //  * Example: create something > as low level user > with correct permissions
    //  * Output:  [Can] create something as low level user with correct permissions
    //  *
    //  * @requires Story
    //  */
    // public function getFullName(): ?string
    // {
    //     if (! $this instanceof Story) {
    //         return null;
    //     }

    //     $fullName = $this->getName();

    //     /**
    //      * Only the most lowest level story should get prefixed with can or cannot
    //      */
    //     if (! $this->hasStories()) {
    //         if ($this->can !== null) {
    //             $can = $this->can ? 'Can' : 'Cannot';

    //             $fullName = "[{$can}] {$fullName}";
    //         }
    //     }

    //     return $fullName;
    // }
}
