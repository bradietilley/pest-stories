<?php

namespace BradieTilley\StoryBoard\Traits;

use Illuminate\Support\Str;

/**
 * @mixin \BradieTilley\StoryBoard\Contracts\WithName
 */
trait HasNameShortcuts
{
    public function view(string $item): static
    {
        return $this->name(
            sprintf('view a %s', $this->getNameShortcut($item))
        );
    }

    public function create(string $item): static
    {
        return $this->name(
            sprintf('create a %s', $this->getNameShortcut($item))
        );
    }

    public function update(string $item): static
    {
        return $this->name(
            sprintf('update a %s', $this->getNameShortcut($item))
        );
    }

    public function delete(string $item): static
    {
        return $this->name(
            sprintf('delete a %s', $this->getNameShortcut($item))
        );
    }

    public function restore(string $item): static
    {
        return $this->name(
            sprintf('restore a %s', $this->getNameShortcut($item))
        );
    }

    private function getNameShortcut(string $item): string
    {
        return (string) Str::of($item)->afterLast('\\');
    }
}
