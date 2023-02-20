<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Contracts;

use BradieTilley\StoryBoard\Story\Tag;

/**
 * This object can be tagged with one or more tags. An example
 * of this is a Story being tagged with GitHub/GitLab issue IDs
 * such as `Issue #12` being `[ 'issue' => 12 ]`.
 *
 * @mixin WithInheritance
 */
interface WithTags
{
    /**
     * Add a tag (or multiple tags) to this object.
     */
    public function tag(string|array|Tag $key, mixed $value = null): static;

    /**
     * Register the tags.
     *
     * When tags are registered, you may append the tags (in name form) to
     * the object's (e.g. story's) name, or perform your own logic.
     *
     * If the tag has a closure-driven value, it will be invoked with the
     * relevant Story/etc.
     */
    public function registerTags(): static;

    /**
     * Append all tags to the story's name (where $this is a Story)
     */
    public function appendTags(): static;

    /**
     * Don't append all tags to the test name (where $this is a Story)
     */
    public function dontAppendTags(): static;

    /**
     * Get all tags against this object.
     *
     * Before inheritance = only the tags directly added to this object.
     * After inheritance = all tags, including those inherited from this object's parents.
     *
     * @return array<Tag>
     */
    public function getTags(): array;

    /**
     * Get all tags as key/value pairs of resolved tag values.
     */
    public function getTagsData(): array;

    /**
     * Get all tags used by this object
     *
     * e.g. [ 'foo' => 'bar', 'something' ]
     *      'foo: bar | something'
     */
    public function getTagsAsName(): string;

    /**
     * Inherit tags from the parent stories.
     */
    public function inheritTags(): void;
}
