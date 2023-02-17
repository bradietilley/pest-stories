<?php

namespace BradieTilley\StoryBoard\Contracts;

use Pest\PendingCalls\TestCall;
use Pest\PendingObjects\TestCall as TestCallDeprecated;

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
     * Asserts that the test throws the given `$exception` class when called.
     *
     * Proxies it to `test('this story test', fn () => ...)->throws()`;
     * If you have specified a different `test` function alias in your config
     * then it must return an object that has the same signature as this method:
     *
     *      throws(string $exception, string $exceptionMessage = null)
     */
    public function throws(string $exception, string $exceptionMessage = null): static;

    /**
     * Asserts that the test throws the given `$exception` class when called if the given $condition is true.
     *
     * Proxies it to `test('this story test', fn () => ...)->throwsIf()`;
     * If you have specified a different `test` function alias in your config
     * then it must return an object that has the same signature as this method:
     *
     *      throwsIf($condition, string $exception, string $exceptionMessage = null)
     *
     * @param (callable(): bool)|bool $condition
     */
    public function throwsIf($condition, string $exception, string $exceptionMessage = null): static;

    /**
     * Forwards previously registered/inherited `throws` and `throwsIf` expectations
     * to the created TestCall (or object that expects throws).
     */
    public function forwardTestCaseShortcutsToTestCall(TestCallDeprecated|TestCall|ExpectsThrows $test): void;

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
