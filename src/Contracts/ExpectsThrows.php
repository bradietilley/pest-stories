<?php

namespace BradieTilley\StoryBoard\Contracts;

/**
 * This class is not used for stories, but used for when you wish to replace
 * the `test` function with another function AND wish to retain the `->throws()`
 * and `->throwsIf()` methods (via `WithTestCaseShortcuts`).
 */
interface ExpectsThrows
{
    /**
     * Asserts that the test throws the given `$exception` class when called.
     */
    public function throws(string $exception, string $exceptionMessage = null): mixed;

    /**
     * Asserts that the test throws the given `$exception` class when called if the given $condition is true.
     *
     * @param (callable(): bool)|bool $condition
     */
    public function throwsIf($condition, string $exception, string $exceptionMessage = null): mixed;
}
