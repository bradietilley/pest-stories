<?php

namespace BradieTilley\StoryBoard\Traits;

/**
 * @property ?static $parent
 */
trait HasInheritance
{
    /**
     * Does this story have a parent?
     */
    public function hasParent(): bool
    {
        return $this->parent !== null;
    }

    /**
     * Get the parent Story
     */
    public function getParent(): ?static
    {
        return $this->parent; /** @phpstan-ignore-line */
    }

    /**
     * Set the parent of this story
     *
     * @param  static  $parent
     */
    public function setParent($parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Inherit a given property from the parent(s)
     */
    public function inheritProperty(string $property, mixed $default = null): mixed
    {
        $instance = $this;

        while ($instance !== null) {
            $value = $instance->getProperty($property);
            $halts = $instance->getPropertyOptional($property.'Halt', false);

            if ($value === null && $halts) {
                return null;
            }

            // Inherit when not null
            if ($value !== null) {
                return $value;
            }

            $instance = $instance->getParent();
        }

        return $default;
    }

    /**
     * Strict-type alias of inheritProperty
     */
    public function inheritPropertyBool(string $property, bool $default = null): ?bool
    {
        /** @var ?bool $value */
        $value = $this->inheritProperty($property, $default);

        return $value;
    }

    /**
     * Get a property from this object
     */
    public function getProperty(string $property): mixed
    {
        return $this->{$property};
    }

    /**
     * Get a property from this object
     */
    public function getPropertyOptional(string $property, mixed $default = null): mixed
    {
        return property_exists($this, $property) ? $this->getProperty($property) : $default;
    }

    /**
     * Get all ancestors starting with $this (child) ending with the grand parent
     *
     * @return array<static>
     */
    public function getAncestors(): array
    {
        $all = [];
        $instance = $this;

        while ($instance !== null) {
            $all[] = $instance;

            $instance = $instance->getParent();
        }

        return $all;
    }
}
