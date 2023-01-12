<?php

namespace BradieTilley\StoryBoard\Contracts;

interface WithNameShortcuts
{
    public function view(string $item): static;

    public function create(string $item): static;

    public function update(string $item): static;

    public function delete(string $item): static;

    public function restore(string $item): static;
}
