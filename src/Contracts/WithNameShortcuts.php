<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Contracts;

/**
 * Shortcut to providing a name to this object in the context
 * of the given typical model-based actions.
 *
 * @mixin WithName
 */
interface WithNameShortcuts
{
    /**
     * Set the name to something relating to viewing the given item
     */
    public function view(string $item): static;

    /**
     * Set the name to something relating to creating the given item
     */
    public function create(string $item): static;

    /**
     * Set the name to something relating to updating the given item
     */
    public function update(string $item): static;

    /**
     * Set the name to something relating to deleting the given item
     */
    public function delete(string $item): static;

    /**
     * Set the name to something relating to restoring the given item
     */
    public function restore(string $item): static;
}
