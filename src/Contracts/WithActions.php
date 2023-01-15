<?php

namespace BradieTilley\StoryBoard\Contracts;

use BradieTilley\StoryBoard\Story\Action;
use BradieTilley\StoryBoard\Story\Result;
use BradieTilley\StoryBoard\Story\StoryAction;
use Closure;

/**
 * This object has actions, expectations and assertions
 *
 * @mixin WithInheritance
 */
interface WithActions
{
    /**
     * Alias for setAction()
     */
    public function action(string|Closure|Action $action, array $arguments = [], int $order = null): static;

    /**
     * Register a callback to run before any actions are booted
     */
    public function before(?Closure $before): static;

    /**
     * Register a callback to run after all actrions are booted
     */
    public function after(?Closure $after): static;

    /**
     * Register a single action for this story.
     * Optionally pass in arguments (matched by name) if the action supports them.
     */
    public function setAction(string|Closure|Action $action, array $arguments = [], int $order = null): static;

    /**
     * Add many actions and have them sorted in the exact order they're provided
     */
    public function sequence(iterable $actions, int $order = 0): static;

    /**
     * Alias for setActions()
     */
    public function actions(iterable $actions): static;

    /**
     * Register multiple actions for this story.
     *
     * The order of each action is inherited from the actions themselves.
     */
    public function setActions(iterable $actions): static;

    /**
     * Get all regsitered actions for this story (no inheritance lookup)
     *
     * @return array<string,StoryAction>
     */
    public function getActions(): array;

    /**
     * Get all actions for this story, including those inherited from parents
     *
     * @return array<string,StoryAction>
     */
    public function resolveInheritedActions(): array;

    /**
     * Resolve all actions that are inherited
     */
    public function registerActions(): static;

    /**
     * Boot all registered actions for this test.
     */
    public function bootActions(): static;

    /**
     * Get all names from all registered actions
     */
    public function getNameFromActions(): ?string;

    /**
     * Registered one or two callbacks to run when the expectation (can or cannot)
     * is applied.
     */
    public function assert(Closure $can = null, Closure $cannot = null): static;

    /**
     * Specify no expectation (reset; block inheritance)
     */
    public function noAssertion(): static;

    /**
     * Specify that you expect that this task 'can run' or 'will pass'
     *
     * The name and callback can be passed in in either order.
     */
    public function setCan(string|Closure|null $name = null, string|Closure|null $callback = null): static;

    /**
     * Specify that you expect that this task 'cannot run' or 'will fail'
     *
     * The name and callback can be passed in in either order.
     */
    public function setCannot(string|Closure|null $name = null, string|Closure|null $callback = null): static;

    /**
     * Get the 'can' / 'cannot' flag for this story
     */
    public function itCan(): ?bool;

    /**
     * Perform the assertions
     */
    public function perform(): static;

    /**
     * Get the result from the task(s) if already run
     */
    public function getResult(): Result;

    /**
     * Inherit all actions from this story's parent
     */
    public function inheritActions(): void;

    /**
     * Inherit assertions from ancestors
     */
    public function inheritAssertions(): void;
}
