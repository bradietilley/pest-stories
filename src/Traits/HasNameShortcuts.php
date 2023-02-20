<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Traits;

use Illuminate\Support\Str;

/**
 * Shortcut to providing a name to this object in the context
 * of the given typical model-based actions.
 *
 * @mixin \BradieTilley\StoryBoard\Contracts\WithName
 */
trait HasNameShortcuts
{
    /**
     * Set the name to something relating to viewing the given item
     */
    public function view(string $item): static
    {
        return $this->name(
            sprintf('view a %s', $this->getNameShortcut($item))
        );
    }

    /**
     * Set the name to something relating to creating the given item
     */
    public function create(string $item): static
    {
        return $this->name(
            sprintf('create a %s', $this->getNameShortcut($item))
        );
    }

    /**
     * Set the name to something relating to updating the given item
     */
    public function update(string $item): static
    {
        return $this->name(
            sprintf('update a %s', $this->getNameShortcut($item))
        );
    }

    /**
     * Set the name to something relating to deleting the given item
     */
    public function delete(string $item): static
    {
        return $this->name(
            sprintf('delete a %s', $this->getNameShortcut($item))
        );
    }

    /**
     * Set the name to something relating to restoring the given item
     */
    public function restore(string $item): static
    {
        return $this->name(
            sprintf('restore a %s', $this->getNameShortcut($item))
        );
    }

    /**
     * Covnert the given item to a human readable name
     */
    private function getNameShortcut(string $item): string
    {
        return (string) Str::of($item)->afterLast('\\');
    }
}
