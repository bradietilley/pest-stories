<?php

namespace BradieTilley\StoryBoard\Traits;

use PHPUnit\Framework\RiskyTestError;

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
     * @var array<string,string>
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
     * Alias of throw new RiskyTestError()
     */
    public function risky(string $message = ''): static
    {
        $this->testCaseShortcuts['risky'] = $message;

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

        foreach ($this->testCaseShortcuts as $key => $value) {
            match ($key) {
                'risky' => throw new RiskyTestError($value),
                'incomplete' => $test->markTestIncomplete($value),
                'skipped' => $test->markTestSkipped($value),
                default => throw new \Exception(sprintf('Unrecognised test case shortcut `%s`', $key)),
            };
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
