<?php

namespace BradieTilley\StoryBoard\Traits;

use Illuminate\Support\Str;

trait HasNameShortcuts
{
    /**
     * @return $this
     */
    public function view(string $item): self
    {
        return $this->name('view a ' . Str::of($item)->afterLast('\\')->toString());
    }

    /**
     * @return $this
     */
    public function create(string $item): self
    {
        return $this->name('create a ' . Str::of($item)->afterLast('\\')->toString());
    }

    /**
     * @return $this
     */
    public function update(string $item): self
    {
        return $this->name('update a ' . Str::of($item)->afterLast('\\')->toString());
    }

    /**
     * @return $this
     */
    public function delete(string $item): self
    {
        return $this->name('delete a ' . Str::of($item)->afterLast('\\')->toString());
    }

    /**
     * @return $this
     */
    public function estore(string $item): self
    {
        return $this->name('restore a ' . Str::of($item)->afterLast('\\')->toString());
    }
}