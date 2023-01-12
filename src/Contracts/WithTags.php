<?php

namespace BradieTilley\StoryBoard\Contracts;

use BradieTilley\StoryBoard\Story\Tag;

interface WithTags
{
    /**
     * Add a tag (or multiple tags) to this object
     */
    public function tag(string|array|Tag $key, mixed $value = null): static;

    /**
     * Inherit tags from the parent stories
     */
    public function inheritTags(): void;

    /**
     * Register the tags
     */
    public function registerTags(): static;

    /**
     * Append all tags to the test name
     */
    public function appendTags(): static;

    /**
     * Don't append all tags to the test name
     *
     * @return $this
     */
    public function dontAppendTags(): static;

    /**
     * Get all tags
     *
     * @return array<Tag>
     */
    public function getTags(): array;

    /**
     * Get all tags as key/value pairs of resolved tag values
     */
    public function getTagsData(): array;

    /**
     * Get all tags used by this object
     *
     * e.g. [ 'foo' => 'bar', 'something' ]
     *      'foo: bar | something'
     */
    public function getTagsAsName(): string;
}
