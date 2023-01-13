<?php

namespace BradieTilley\StoryBoard\Contracts;

interface WithTestCaseShortcuts
{
    public function skipped(string $message = ''): static;

    public function incomplete(string $message = ''): static;

    public function risky(): static;

    public function inheritTestCaseShortcuts(): void;

    public function getTestCaseShortcuts(): array;

    public function bootTestCaseShortcuts(): void;
}
