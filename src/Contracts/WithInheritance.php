<?php

namespace BradieTilley\StoryBoard\Contracts;

interface WithInheritance
{
    /**
     * Does this story have a parent?
     */
    public function hasParent(): bool;

    /**
     * Get the parent Story
     *
     * @return static
     */
    public function getParent(): ?static;

    /**
     * Set the parent of this story
     *
     * @param  static  $parent
     */
    public function setParent(self $parent): static;

    /**
     * Inherit a given property from the parent(s)
     */
    public function inheritProperty(string $property, mixed $default = null): mixed;

    /**
     * Strict-type alias of inheritProperty
     */
    public function inheritPropertyBool(string $property, bool $default = null): ?bool;

    /**
     * Get a property from this object
     */
    public function getProperty(string $property): mixed;

    /**
     * Get a property from this object
     */
    public function getPropertyOptional(string $property, mixed $default = null): mixed;

    /**
     * Get all ancestors starting with $this (child) ending with the grand parent
     *
     * @return array<static>
     */
    public function getAncestors(): array;
}
