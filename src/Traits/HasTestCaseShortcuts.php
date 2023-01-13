<?php

namespace BradieTilley\StoryBoard\Traits;

use PHPUnit\Framework\RiskyTestError;

/**
 * @mixin \BradieTilley\StoryBoard\Contracts\WithTestCaseShortcuts
 * @mixin \BradieTilley\StoryBoard\Story
 */
trait HasTestCaseShortcuts
{
    protected array $testCaseShortcuts = [];

    public function skipped(string $message = ''): static
    {
        $this->testCaseShortcuts['skipped'] = $message;

        return $this;
    }

    public function incomplete(string $message = ''): static
    {
        $this->testCaseShortcuts['incomplete'] = $message;

        return $this;
    }

    public function risky(): static
    {
        $this->testCaseShortcuts['risky'] = true;

        return $this;
    }

    public function inheritTestCaseShortcuts(): void
    {
        $all = [];

        foreach (array_reverse($this->getAncestors()) as $level) {
            $all = array_replace($all, (array) $level->getProperty('testCaseShortcuts'));
        }

        $this->testCaseShortcuts = $all;
    }

    public function getTestCaseShortcuts(): array
    {
        return $this->testCaseShortcuts;
    }

    public function bootTestCaseShortcuts(): void
    {
        $test = $this->getTest();

        if ($test === null) {
            return;
        }

        foreach ($this->testCaseShortcuts as $key => $value) {
            match ($key) {
                'risky' => throw new RiskyTestError('This story is risky'),
                'incomplete' => $test->markTestIncomplete($value),
                'skipped' => $test->markTestSkipped($value),
                default => throw new \Exception(sprintf('Unrecognised test case shortcut `%s`', $key)),
            };
        }
    }
}
