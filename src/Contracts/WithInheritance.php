<?php

namespace BradieTilley\StoryBoard\Contracts;

/**
 * This object has hierarchy (parent -> children -> etc) and
 * can inherit properties from its parent(s)
 */
interface WithInheritance
{
    /**
     * Does this object have a parent?
     */
    public function hasParent(): bool;

    /**
     * Get the parent of this object
     */
    public function getParent(): ?static;

    /**
     * Set the parent of this object
     *
     * @param  static  $parent Parent must be of the same class type
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
