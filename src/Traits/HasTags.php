<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Tag;
use Illuminate\Support\Collection;

trait HasTags
{
    /**
     * All registered tags for this object
     *
     * @var array<string, Tag>
     */
    protected array $tags = [];

    /**
     * Should tags be appended to the story's test name?
     */
    protected ?bool $appendTags = null;

    /**
     * Add a tag (or multiple tags) to this object
     *
     * @return $this
     */
    public function tag(string|array|Tag $key, mixed $value = null): self
    {
        if ($key instanceof Tag) {
            $key = [
                $key->getName() => $key,
            ];
        }

        if (! is_array($key)) {
            $key = [
                $key => $value,
            ];
        }

        foreach ($key as $tagName => $tagValue) {
            if (is_int($tagName)) {
                $tagName = $tagValue;
            }

            $tag = ($tagValue instanceof Tag) ? $tagValue : new Tag($tagName, $tagValue);

            $this->tags[$tag->getName()] = $tag;
        }

        return $this;
    }

    /**
     * Inherit tags from the parent stories
     */
    public function inheritTags(): void
    {
        /** @var Story $this */
        $tags = [];

        foreach (array_reverse($this->getAncestors()) as $level) {
            $tags = array_replace($tags, $level->getProperty('tags'));
        }

        $this->tags = Collection::make($tags)->sortBy(fn (Tag $tag) => $tag->getOrder())->all();

        $this->appendTags = $this->inheritProperty('appendTags');
    }

    /**
     * Register the tags
     */
    public function registerTags(): self
    {
        foreach ($this->tags as $tag) {
            $tag->register($this)->boot($this);
        }

        return $this;
    }

    /**
     * Append all tags to the test name
     *
     * @return $this
     */
    public function appendTags(): self
    {
        $this->appendTags = true;

        return $this;
    }

    /**
     * Don't append all tags to the test name
     *
     * @return $this
     */
    public function dontAppendTags(): self
    {
        $this->appendTags = false;

        return $this;
    }

    /**
     * Get all tags
     *
     * @return array<Tag>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Get all tags as key/value pairs of resolved tag values
     */
    public function getTagsData(): array
    {
        return Collection::make($this->tags)
            ->map(fn (Tag $tag) => $tag->getValue())
            ->all();
    }

    /**
     * Get all tags used by this object
     *
     * e.g. [ 'foo' => 'bar', 'something' ]
     *      'foo: bar | something'
     */
    public function getTagsAsName(): string
    {
        return Collection::make($this->tags)->map(fn (Tag $tag) => (string) $tag)->implode(' | ');
    }
}
