<?php

namespace BradieTilley\StoryBoard\Contracts;

use Closure;
use PHPUnit\Framework\TestCase;

interface WithTest
{
    /**
     * Register this story actions
     */
    public function register(): static;

    /**
     * Boot (and register) the story and its actions
     */
    public function boot(): static;

    /**
     * Get the test case used for this story
     */
    public function getTest(): ?TestCase;

    /**
     * Set the test case used for this story
     */
    public function setTest(TestCase $test): static;

    /**
     * Create test cases for all tests
     */
    public function test(): static;

    /**
     * Create a test case for this story (e.g. create a `test('name', fn () => ...)`)
     */
    public function testSingle(): static;

    /**
     * Get the name of this test
     */
    public function getTestName(): string;

    /**
     * Inherit all properties that are inheritable
     */
    public function inherit(): static;

    /**
     * Inherit assertions from ancestord
     */
    public function inheritAssertions(): void;

    /**
     * Run this story from start to finish
     */
    public function run(): static;

    /**
     * Run the setUp callback
     */
    public function runSetUp(): void;

    /**
     * Run the tearDown callback
     */
    public function runTearDown(array $args = []): void;

    /**
     * Run the full test assertion (after setTest)
     */
    public function fullRun(): static;

    /**
     * Register a callback to run when the test is set up
     */
    public function setUp(?Closure $callback): static;

    /**
     * Register a callback to run when the test the teared down
     */
    public function tearDown(?Closure $callback): static;
}
