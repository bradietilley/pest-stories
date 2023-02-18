<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Contracts\ExpectsThrows;
use Pest\PendingCalls\TestCall;

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
 * @mixin \BradieTilley\StoryBoard\Contracts\WithTestCaseShortcuts
 * @mixin \BradieTilley\StoryBoard\Story
 */
trait HasTestCaseShortcuts
{
    /**
     * Pending list of test case shortcuts to apply
     *
     * @var array<string,string|array>
     */
    protected array $testCaseShortcuts = [];

    /**
     * Alias of TestCase::markTestSkipped().
     */
    public function skipped(string $message = ''): static
    {
        $this->testCaseShortcuts['skipped'] = $message;

        return $this;
    }

    /**
     * Alias of TestCase::markTestIncomplete().
     */
    public function incomplete(string $message = ''): static
    {
        $this->testCaseShortcuts['incomplete'] = $message;

        return $this;
    }

    /**
     * Asserts that the test throws the given `$exception` class when called.
     *
     * Proxies it to `test('this story test', fn () => ...)->throws()`;
     * If you have specified a different `test` function alias in your config
     * then it must return an object that has the same signature as this method:
     *
     *      throws(string $exception, string $exceptionMessage = null)
     */
    public function throws(string $exception, string $exceptionMessage = null): static
    {
        $this->testCaseShortcuts['throws'] = [
            'exception' => $exception,
            'exceptionMessage' => $exceptionMessage,
        ];

        return $this;
    }

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
    public function throwsIf($condition, string $exception, string $exceptionMessage = null): static
    {
        $this->testCaseShortcuts['throwsIf'] = [
            'condition' => $condition,
            'exception' => $exception,
            'exceptionMessage' => $exceptionMessage,
        ];

        return $this;
    }

    /**
     * Get this story's TestCase shortcuts
     */
    public function getTestCaseShortcuts(): array
    {
        return $this->testCaseShortcuts;
    }

    /**
     * Boot (i.e. run) this story's TestCase shortcuts.
     */
    public function bootTestCaseShortcuts(): void
    {
        $test = $this->getTest();

        if ($test === null) {
            return;
        }

        /**
         * Get the incomplete() message
         */
        $incomplete = $this->testCaseShortcuts['incomplete'] ?? null;

        if (is_string($incomplete)) {
            $test->markTestIncomplete($incomplete);
        }

        /**
         * Get the skipped() message
         */
        $skipped = $this->testCaseShortcuts['skipped'] ?? null;

        if (is_string($skipped)) {
            $test->markTestSkipped($skipped);
        }
    }

    /**
     * Forwards previously registered/inherited `throws` and `throwsIf` expectations
     * to the created TestCall (or object that expects throws).
     */
    public function forwardTestCaseShortcutsToTestCall(TestCall|ExpectsThrows $test): void
    {
        /**
         * Add ->throws() to TestCall if `throws` was registered
         */
        $throws = $this->testCaseShortcuts['throws'] ?? null;

        if (is_array($throws)) {
            /** @var class-string $exception */
            $exception = $throws['exception'];

            /** @var ?string $exceptionMessage */
            $exceptionMessage = $throws['exceptionMessage'];

            // Add expected throw
            $test->throws($exception, $exceptionMessage);
        }

        /**
         * Add ->throwsIf() to TestCall if `throwsIf` was registered
         */
        $throws = $this->testCaseShortcuts['throwsIf'] ?? null;

        if (is_array($throws)) {
            /** @var callable|bool $condition */
            $condition = $throws['condition'];

            // Convert callable to boolean
            if (is_callable($condition)) {
                $condition = (bool) static::call($condition, $this->getParameters($throws));
            }

            /** @var class-string $exception */
            $exception = $throws['exception'];

            /** @var ?string $exceptionMessage */
            $exceptionMessage = $throws['exceptionMessage'];

            // Add expected throw
            $test->throwsIf($condition, $exception, $exceptionMessage);
        }
    }

    /**
     * Inherit any TestCase shortcuts from this story's parents
     */
    public function inheritTestCaseShortcuts(): void
    {
        $all = [];

        foreach (array_reverse($this->getAncestors()) as $level) {
            $all = array_replace($all, (array) $level->getProperty('testCaseShortcuts'));
        }

        /** @var array<string, string> $all */
        $this->testCaseShortcuts = $all;
    }
}
