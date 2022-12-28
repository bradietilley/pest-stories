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

        while ($instance !== null) {
            /** @var static $instance */
            $value = $instance->{$getterMethod}();
            $passes = ($inheritsWhen instanceof Closure) ? $inheritsWhen($this, $value) : ($value !== null);

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
            $all->push($value);
        }

        return $all->collapse()->all();
    }
}
