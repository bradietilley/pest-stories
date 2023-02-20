<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Contracts;

use BradieTilley\StoryBoard\Story;
use Illuminate\Support\Collection;

/**
 * This object (Story) has one or more child stories.
 *
 * @mixin WithName
 */
interface WithStories
{
    /**
     * Get stories (direct-children) as a collection.
     *
     * @see ->getStories()
     *
     * @return Collection<int, Story>
     */
    public function collectGetStories(): Collection;

    /**
     * Get stories (child-most) as a collection.
     *
     * @see ->allStories()
     *
     * @return Collection<string, Story>
     */
    public function collectAllStories(): Collection;

    /**
     * Alias of setStories()
     *
     * @param  Story|array<Story>  $stories
     */
    public function stories(...$stories): static;

    /**
     * Add one or more stories (children) to this object/Story.
     *
     * @param  Story|array<Story>  $stories
     */
    public function setStories(...$stories): static;

    /**
     * Get the direct-children stories.
     *
     * Scenario:
     *     Grandparent -> Parents -> Children
     * Example:
     *     $grandparent->getStories(); // $parents (not $children)
     *
     * @return array<Story>
     */
    public function getStories(): array;

    /**
     * Does this Story have children stories?
     */
    public function hasStories(): bool;

    /**
     * Performs a deep search for all child-most stories under this story.
     *
     * This is used internally to identify all the stories that will be used as tests,
     * as parents are ignored (only used to inherit).
     *
     * @return array<Story>
     */
    public function nestedStories(): array;

    /**
     * Get the stories under this story in key (story name) value (Story object) pairs
     *
     * If this story has no child stories, it returns itself.
     * If this story has child stories then it returns its children only.
     *
     * Scenario:
     *     Grandparent -> Parents -> Children
     * Example:
     *     $grandparent->allStories(); // $children
     *     $parent->allStories();      // $children
     *     $child->allStories();       // $child (itself)
     *
     * @return array<string,Story>
     */
    public function allStories(): array;
}
