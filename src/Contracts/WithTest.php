<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Contracts;

use BradieTilley\StoryBoard\Enums\StoryStatus;
use Closure;
use PHPUnit\Framework\TestCase;

/**
 * This object (story) has facility to register a test via
 * the `->test()` method. Once the test is registered and Pest
 * invokes it, the TestCase can be provided via the `->setTest()`
 * method and later retrieved via `->getTest()`.
 *
 * Therefore, during the registration/creation of a Story test,
 * there is no TestCase available. Any even after registration
 * will have access to the TestCase.
 */
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

    /**
     * Get the status of the test
     */
    public function getStatus(): StoryStatus;
}
