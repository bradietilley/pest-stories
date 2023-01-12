<?php

namespace BradieTilley\StoryBoard\Contracts;

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

interface WithStories
{
    /**
     * Get stories (direct-children) as a collection
     *
     * @see ->getStories()
     *
     * @return Collection<int,Story>
     */
    public function collectGetStories(): Collection;

    /**
     * Get stories (child-most) as a collection.
     *
     * @see ->allStories()
     *
     * @return Collection<string,Story>
     */
    public function collectAllStories(): Collection;

    /**
     * Alias of setStories()
     *
     * @param  Story|array<Story>  $stories
     */
    public function stories(...$stories): static;

    /**
     * Add stories
     *
     * @param  Story|array<Story>  $stories
     */
    public function setStories(...$stories): static;

    /**
     * Get stories
     *
     * @return array<Story>
     */
    public function getStories(): array;

    /**
     * Does this Story/Storyboard have children stories?
     */
    public function hasStories(): bool;

    /**
     * Get all nested stories
     *
     * @return array<Story>
     */
    public function nestedStories(): array;

    /**
     * Get the stories under this story.
     *
     * If this story has no child stories, it returns itself.
     * If this story has child stories then it returns its children only.
     *
     * @requires HasName
     *
     * @return array<string,Story>
     */
    public function allStories(): array;
}
