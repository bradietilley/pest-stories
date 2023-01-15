<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Exceptions\InvalidMagicAliasException;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story;
use Illuminate\Support\Collection;

/**
 * This object (Story) has one or more child stories.
 *
 * @property-read Collection<int,Story> $storiesDirect
 * @property-read Collection<string,Story> $storiesAll
 *
 * @mixin \BradieTilley\StoryBoard\Contracts\WithName
 */
trait HasStories
{
    /**
     * List of all children stories.
     *
     * Before registering, this is the direct child you specify.
     * After registering, this is all children/grandchildren.
     */
    protected array $stories = [];

    /**
     * Property getter(s) for Stories trait
     */
    public function __getStories(string $name): mixed
    {
        if ($name === 'storiesAll') {
            return $this->collectAllStories();
        }

        if ($name === 'storiesDirect') {
            return $this->collectGetStories();
        }

        throw StoryBoardException::invalidMagicAliasException($name, InvalidMagicAliasException::TYPE_PROPERTY);
    }

    /**
     * Get stories (direct-children) as a collection
     *
     * @see ->getStories()
     *
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
     *
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
     */
    public function stories(...$stories): static
    {
        return $this->setStories(...$stories);
    }

    /**
     * Add one or more stories (children) to this object/Story.
     *
     * @param  Story|array<Story>  $stories
     */
    public function setStories(...$stories): static
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
     * Get the direct-children stories.
     *
     * Scenario:
     *     Grandparent -> Parents -> Children
     * Example:
     *     $grandparent->getStories(); // $parents (not $children)
     *
     * @return array<Story>
     */
    public function getStories(): array
    {
        return $this->stories;
    }

    /**
     * Does this Story have children stories?
     */
    public function hasStories(): bool
    {
        return ! empty($this->stories);
    }

    /**
     * Performs a deep search for all child-most stories under this story.
     *
     * This is used internally to identify all the stories that will be used as tests,
     * as parents are ignored (only used to inherit).
     *
     * @return array<Story>
     */
    public function nestedStories(): array
    {
        $children = [];

        foreach ($this->getStories() as $story) {
            if ($story->hasStories()) {
                $children = array_merge($children, $story->nestedStories());
            } else {
                $children[] = $story;
            }
        }

        return $children;
    }

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
    public function allStories(): array
    {
        $stories = $this->nestedStories();

        foreach ($stories as $story) {
            $story->inherit();
        }

        $children = Collection::make($stories)->keyBy(fn (Story $story) => $story->getTestName())->all();

        return $children;
    }
}
