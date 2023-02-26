<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Contracts\WithAssertions;
use function BradieTilley\StoryBoard\debug;
use function BradieTilley\StoryBoard\error;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story\Action;
use BradieTilley\StoryBoard\Story\Assertion;
use BradieTilley\StoryBoard\Story\Result;
use BradieTilley\StoryBoard\Story\StoryAction;
use Closure;
use Illuminate\Support\Collection;
use Throwable;

/**
 * This object has actions, expectations and assertions
 *
 * @method static can(string|Closure|Assertion|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static cannot(string|Closure|Assertion|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static static can(string|Closure|Assertion|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static static cannot(string|Closure|Assertion|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 *
 * @mixin \BradieTilley\StoryBoard\Story
 */
trait HasActions
{
    protected ?Result $result = null;

    /**
     * All actions and their arguments (excluding inheritance until story is registered)
     *
     * @var array<string,StoryAction>
     */
    protected array $actions = [];

    /**
     * Alias for setAction()
     */
    public function action(string|Closure|Action $action, array $arguments = [], int $order = null): static
    {
        return $this->setAction($action, $arguments, $order);
    }

    /**
     * Register a callback to run before actions are run
     */
    public function before(?Closure $before): static
    {
        return $this->setCallback('before', $before);
    }

    /**
     * Register a callback to run after actions are run
     */
    public function after(?Closure $after): static
    {
        return $this->setCallback('after', $after);
    }

    /**
     * Register a single action for this story.
     * Optionally pass in arguments (matched by name) if the action supports them.
     */
    public function setAction(string|Closure|Action $action, array $arguments = [], int $order = null): static
    {
        $action = Action::prepare($action);

        $storyAction = new StoryAction(
            story: $this,
            action: $action,
            arguments: $arguments,
            order: $order,
        );

        $this->actions[$action->getName()] = $storyAction;

        return $this;
    }

    /**
     * Add many actions and have them sorted in the exact order they're provided
     */
    public function sequence(iterable $actions, int $order = 0): static
    {
        foreach ($actions as $action => $arguments) {
            // Closures and classes will be int key
            if (is_string($arguments) || ($arguments instanceof Closure) || ($arguments instanceof Action)) {
                $action = $arguments;
                $arguments = [];
            }

            $this->setAction($action, $arguments, order: ++$order);
        }

        return $this;
    }

    /**
     * Alias for setActions()
     */
    public function actions(iterable $actions): static
    {
        return $this->setActions($actions);
    }

    /**
     * Register multiple actions for this story.
     *
     * The order of each action is inherited from the actions themselves.
     */
    public function setActions(iterable $actions): static
    {
        foreach ($actions as $action => $arguments) {
            // Closures and classes will be int key
            if (is_string($arguments) || ($arguments instanceof Closure) || ($arguments instanceof Action)) {
                $action = $arguments;
                $arguments = [];
            }

            $this->setAction($action, $arguments);
        }

        return $this;
    }

    /**
     * Get all regsitered actions for this story (no inheritance lookup)
     *
     * @return array<string,StoryAction>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Get all actions for this story, including those inherited from parents
     *
     * @return array<string,StoryAction>
     */
    public function resolveInheritedActions(): array
    {
        $all = [];

        foreach (array_reverse($this->getAncestors()) as $ancestor) {
            foreach ($ancestor->getActions() as $name => $storyAction) {
                $all[$name] = (clone $storyAction)->withStory($this);
            }
        }

        return $all;
    }

    /**
     * Resolve all actions that are inherited
     */
    public function registerActions(): static
    {
        $this->actions = Collection::make($this->actions)
            ->sortBy(fn (StoryAction $storyAction) => $storyAction->getOrder())
            ->all();

        foreach ($this->actions as $storyAction) {
            $storyAction->register();
        }

        return $this;
    }

    /**
     * Boot all registered actions for this test.
     */
    public function bootActions(): static
    {
        if (empty($this->actions)) {
            throw StoryBoardException::actionNotSpecified($this);
        }

        $result = $this->getResult();

        try {
            $resultData = [
                'result' => $result->getValue(),
            ];

            $this->runCallback('before', $this->getParameters($resultData));

            foreach ($this->actions as $storyAction) {
                // Run action get result
                $value = $storyAction->boot($this->getParameters($resultData));

                // Set the variable
                $this->setData($storyAction->getVariable(), $value);

                // Set the result
                $result->setValue($value);

                $resultData = [
                    'result' => $value,
                ];
            }

            /* Call after listener */
            $this->runCallback('after', $this->getParameters($resultData));
        } catch (Throwable $e) {
            error('Failed to boot actions with error', $e);

            $result->setError($e);

            throw $e;
        }

        debug('Successfully booted actions');

        return $this;
    }

    /**
     * Get all names from all registered actions
     */
    public function getNameFromActions(): ?string
    {
        // Just this level
        $actions = Collection::make($this->actions)
            ->map(fn (StoryAction $storyAction) => $storyAction->getAppendName())
            ->filter();

        return $actions->isNotEmpty() ? $actions->implode(' ') : null;
    }

    /**
     * Perform the assertions
     */
    public function perform(): static
    {
        if ($this->skipDueToIsolation()) {
            $test = $this->getTest();

            if ($test) {
                // @codeCoverageIgnoreStart
                $test->markTestSkipped('Isolation Mode Enabled');
                // @codeCoverageIgnoreEnd
            }

            return $this;
        }

        assert($this instanceof WithAssertions);

        $this->runAssertions();

        return $this;
    }

    /**
     * Get the result from the task(s) if already run
     */
    public function getResult(): Result
    {
        return $this->result ??= new Result();
    }

    /**
     * Inherit all actions from this story's parent
     */
    public function inheritActions(): void
    {
        $this->actions = $this->resolveInheritedActions();
    }
}
