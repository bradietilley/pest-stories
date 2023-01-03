<?php

namespace BradieTilley\StoryBoard\Traits;

use Closure;
use Illuminate\Support\Collection;

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
     *
     * @return static
     */
    public function getParent(): ?static
    {
        return $this->parent;
    }

    /**
     * Set the parent of this story
     *
     * @param  static  $parent
     * @return $this
     */
    public function setParent(self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get a value from this current object, or from its closest parent.

     *
     * @param  Closure|null  $inheritsWhen
     *                        When null, the value will be inherited from the parent when the resolved value is not null
     *                        When closure, the value will be inherited from the parent when this callback returns true
     */
    public function inheritFromParents(string $getterMethod, Closure|null $inheritsWhen = null, mixed $default = null): mixed
    {
        $instance = $this;

        $haltGetterMethod = $getterMethod . 'Halt';
        $haltGetterMethodExists = method_exists($this, $haltGetterMethod);

        while ($instance !== null) {
            /** @var static $instance */
            $value = $instance->{$getterMethod}();
            $passes = ($inheritsWhen instanceof Closure) ? $inheritsWhen($this, $value) : ($value !== null);

            // If at first it fails (is null, etc), check to see if we should halt the lookup
            if ($passes === false) {
                // if the halt flag method exists (e.g. 'getCan' -> 'getCanHalt')
                if ($haltGetterMethodExists) {
                    // if the halt flag is true then we should take the value as-is, even if it's null.
                    if ($instance->{$haltGetterMethod}() === true) {
                        $passes = true;
                    }
                }
            }

            if ($passes === true) {
                return $value;
            }

            $instance = $instance->getParent();
        }

        return $default;
    }

    /**
     * Combine all values from this current object, and from its parents.
     */
    public function combineFromParents(string $getterMethod): array
    {
        $instance = $this;
        $all = Collection::make([]);

        $instances = [];

        while ($instance !== null) {
            /** @var static $instance */
            $instances[] = $instance;
            $instance = $instance->getParent();
        }

        /** @var array<static> $instances */
        $instances = array_reverse($instances);

        foreach ($instances as $instance) {
            $value = $instance->{$getterMethod}();

            if (! is_iterable($value)) {
                $value = [$value];
            }

            $all->push($value);
        }

        return $all->collapse()->all();
    }

    public function inheritProperty(string $property, ?Closure $inheritsWhen = null, mixed $default = null): mixed
    {
        $instance = $this;

        while ($instance !== null) {
            $value = $instance->getProperty($property);

            if ($inheritsWhen === null) {
                // Inherit when not null

                if ($value !== null) {
                    return $value;
                }
            } else {
                // Inherit when not closure returns true

                if ($inheritsWhen($value)) {
                    return $value;
                }
            }

            $instance = $instance->getParent();
        }

        return $default;
    }

    /**
     * Get a property from this object
     */
    public function getProperty(string $property): mixed
    {
        return $this->{$property};
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
