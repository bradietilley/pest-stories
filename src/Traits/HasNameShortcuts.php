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
        return $this->name('view a '.Str::of($item)->afterLast('\\')->toString());
    }

    public function create(string $item): static
    {
        return $this->name('create a '.Str::of($item)->afterLast('\\')->toString());
    }

    public function update(string $item): static
    {
        return $this->name('update a '.Str::of($item)->afterLast('\\')->toString());
    }

    public function delete(string $item): static
    {
        return $this->name('delete a '.Str::of($item)->afterLast('\\')->toString());
    }

    public function restore(string $item): static
    {
        return $this->name('restore a '.Str::of($item)->afterLast('\\')->toString());
    }
}
