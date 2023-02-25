<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Contracts;

use BradieTilley\StoryBoard\Enums\Expectation;
use BradieTilley\StoryBoard\Story\Assertion;
use BradieTilley\StoryBoard\Story\StoryAssertion;
use Closure;

/**
 * This object has actions, expectations and assertions
 *
 * @mixin WithInheritance
 */
interface WithAssertions
{
    /**
     * Alias for setAssertion()
     */
    public function assertion(string|Closure|Assertion $assertion, array $arguments = [], int $order = null, Expectation $expectation = null): static;

    /**
     * Register a single assertion for this story.
     * Optionally pass in arguments (matched by name) if the assertion supports them.
     */
    public function setAssertion(string|Closure|Assertion $assertion, array $arguments = [], int $order = null, Expectation $expectation = null): static;

    /**
     * Get the default expectation key to append assertions to.
     */
    public function getCurrentExpectationKey(): string;

    /**
     * Alias for setAssertions()
     */
    public function assertions(iterable $assertions, Expectation $expectation = null): static;

    /**
     * Register multiple assertions for this story.
     *
     * The order of each assertion is inherited from the assertions themselves.
     */
    public function setAssertions(iterable $assertions, Expectation $expectation = null): static;

    /**
     * Get all regsitered assertions for this story (no inheritance lookup)
     *
     * @return array<string,array<int,StoryAssertion>>
     */
    public function getAssertions(): array;

    /**
     * Define the given assertions for can cannot or any scenario
     */
    public function assert(
        string|Closure|Assertion $can = null,
        string|Closure|Assertion $cannot = null,
        string|Closure|Assertion $always = null,
    ): static;

    /**
     * Assert that when this is flagged as `can()` that the given assertion
     * will pass.
     */
    public function whenCan(string|Closure|Assertion $assertion): static;

    /**
     * Assert that when this is flagged as `cannot()` that the given assertion
     * will pass.
     */
    public function whenCannot(string|Closure|Assertion $assertion): static;

    /**
     * Assert that this given assertion will always pass
     */
    public function whenAlways(string|Closure|Assertion $assertion): static;

    /**
     * Reset the expectation
     */
    public function resetExpectation(): static;

    /**
     * Get the 'can' / 'cannot' flag for this story
     */
    public function itCan(): ?bool;

    /**
     * Inherit assertions from ancestors
     */
    public function inheritAssertions(): void;
}
