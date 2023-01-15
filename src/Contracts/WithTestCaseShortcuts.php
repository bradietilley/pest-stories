<?php

namespace BradieTilley\StoryBoard\Contracts;

/**
 * Allows a shortcut to running various TestCase-specific methods.
 *
 * Without this, you would need:
 *
 *      Story::make()->action(fn (TestCase $test) => $test->markSkipped('Blah'));
 *
 * With this, you can do:
 *
 *      Story::make()->skipped('Blah');
 *
 * @mixin WithInheritance
 */
interface WithTestCaseShortcuts
{
    /**
     * Alias of TestCase::markTestSkipped().
     */
    public function skipped(string $message = ''): static;

    /**
     * Alias of TestCase::markTestIncomplete().
     */
    public function incomplete(string $message = ''): static;

    /**
     * Alias of throw new RiskyTestError()
     */
    public function risky(): static;

    /**
     * Get this story's TestCase shortcuts
     */
    public function getTestCaseShortcuts(): array;

    /**
     * Boot (i.e. run) this story's TestCase shortcuts.
     */
    public function bootTestCaseShortcuts(): void;

    /**
     * Inherit any TestCase shortcuts from this story's parents
     */
    public function inheritTestCaseShortcuts(): void;
}
