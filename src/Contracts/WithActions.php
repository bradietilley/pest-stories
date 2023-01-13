<?php

namespace BradieTilley\StoryBoard\Contracts;

use BradieTilley\StoryBoard\Story\Action;
use BradieTilley\StoryBoard\Story\Result;
use BradieTilley\StoryBoard\Story\StoryAction;
use Closure;

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
     * @requires HasInheritance
     *
     * @return array<string,StoryAction>
     */
    public function allActions(): array;

    /**
     * Inherit all actions from this story's parent
     */
    public function inheritActions(): void;

    /**
     * Resolve all actions that are inherited
     */
    public function registerActions(): static;

    /**
     * Boot all registered actions for this test.
     *
     * @requires HasInheritance
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
     * Set whether this task can run (i.e. passes)
     */
    public function can(bool|Closure $can = true): static;

    /**
     * Set that this task cannot run (i.e. fails)
     */
    public function cannot(?Closure $cannot = null): static;

    /**
     * Get the 'can' / 'cannot' flag for this story
     */
    public function itCan(): ?bool;

    /**
     * Perform the assertions
     *
     * @requires Story
     */
    public function perform(): static;

    /**
     * Get the result from the task(s) if already run
     */
    public function getResult(): Result;
}
