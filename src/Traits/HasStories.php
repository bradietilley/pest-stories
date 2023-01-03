<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

trait HasStories
{
    protected array $stories = [];

    /**
     * Get stories (direct-children) as a collection
     * 
     * @see ->getStories()
     * @return Collection<int,Story>
     */
    public function collectGetStories(): Collection
    {
        return Collection::make($this->stories);
    }

    /**
     * Get stories (child-most) as a collection.
     * 
     * @see ->allStories()
     * @return Collection<string,Story>
     */
    public function collectAllStories(): Collection
    {
        return Collection::make($this->allStories());
    }

    /**
     * Alias of setStories()
     *
     * @param  Story|array<Story>  $stories
     * @return $this
     */
    public function stories(...$stories): self
    {
        return $this->setStories(...$stories);
    }

    /**
     * Add stories
     *
     * @param  Story|array<Story>  $stories
     * @return $this
     */
    public function setStories(...$stories): self
    {
        foreach ($stories as $storyList) {
            $storyList = (is_array($storyList)) ? $storyList : [$storyList];

            foreach ($storyList as $story) {
                if (! ($story instanceof Story)) {
                    throw StoryBoardException::invalidStory();
                }

                $this->stories[] = $story->setParent($this);
            }
        }

        return $this;
    }

    /**
     * Get stories
     *
     * @return array<Story>
     */
    public function getStories(): array
    {
        return $this->stories;
    }

    /**
     * Does this Story/Storyboard have children stories?
     */
    public function hasStories(): bool
    {
        return ! empty($this->stories);
    }

    public function nestedStories(): array
    {
        /** @var HasName|HasStories $this */

        $children = [];

        foreach ($this->getStories() as $story) {
            if ($story->hasStories()) {
                $children = array_merge($children, $story->nestedStories());
            } else {
                $children[] = $this;
            }
        }

        return $children;
    }

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
    public function allStories(): array
    {
        /** @var HasName|HasStories $this */

        $stories = $this->nestedStories();

        foreach ($stories as $story) {
            $story->inherit();
        }

        $children = Collection::make($stories)->keyBy(fn (Story $story) => $story->getFullName())->all();

        return $children;
    }
}
