<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story;
use Illuminate\Support\Collection;
use InvalidArgumentException;

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
                    throw new InvalidArgumentException('You must only provide Story classes to the stories() method.');
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
        return !empty($this->stories);
    }

    /**
     * Get the stories under this story.
     * 
     * If this story has no child stories, it returns itself.
     * If this story has child stories then it returns its children only.
     * 
     * @return array<string,Story>
     */
    public function all(): array
    {
        /** @var Story|self|HasName $this */

        if (empty($this->stories)) {
            return [
                $this->getFullName() => $this,
            ];
        }

        $children = Collection::make($this->getStories())
            ->map(
                fn (Story $story) => $story->all()
            )
            ->collapse()
            ->keyBy(
                fn (Story $story) => $story->getFullName(),
            )
            ->all();

        return $children;
    }
}