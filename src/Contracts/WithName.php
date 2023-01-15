<?php

namespace BradieTilley\StoryBoard\Contracts;

/**
 * This object has a name
 *
 * @mixin WithInheritance
 */
interface WithName
{
    /**
     * Run when parent class is cloned; name may need updating?
     */
    public function __cloneName(): void;

    /**
     * Alias for setName()
     */
    public function name(string $name): static;

    /**
     * Set the name of this story
     */
    public function setName(string $name): static;

    /**
     * Get the name of this story
     */
    public function getName(): ?string;

    /**
     * Require the name of this story.
     */
    public function getNameString(): string;

    /**
     * Inherit the name from parents
     */
    public function inheritName(): void;

    /**
     * Get full name, without:
     *
     * - expectation (e.g. `[Can]`)
     * - tags (e.g. `issue: 123`)
     */
    public function getFullName(): string;

    /**
     * Get the name of this ancestory level
     */
    public function getLevelName(): string;
}
