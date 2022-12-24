<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story;
use Illuminate\Support\Collection;

trait HasStories
{
    protected array $stories = [];

    /**
     * Add stories
     * 
     * @param Story|array<Story> $stories
     * @return $this 
     */
    public function stories(...$stories): self
    {
        /** @var self|Story $this */

        foreach ($stories as $storyList) {
            $storyList = (is_array($storyList)) ? $storyList : [$storyList];

            foreach ($storyList as $story) {
                if (! ($story instanceof Story)) {
                    //
                }

                $story->setParent($this);

                $this->stories[] = $story;
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
     */
    public function hasStories(): bool
    {
        return !empty($this->stories);
    }

    /**
     * Get all stories
     * 
     * @return array<string,Story>
     */
    public function allStories(): array
    {
        /** @var Story|self|HasName $this */

        if (empty($this->stories)) {
            return [
                $this->getFullName() => $this,
            ];
        }

        $children = Collection::make($this->getStories())
            ->map(
                fn (Story $story) => $story->allStories()
            )
            ->collapse()
            ->keyBy(
                fn (Story $story) => $story->getFullName(),
            )
            ->all();

        return $children;
    }
}