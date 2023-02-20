<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Traits;

use function BradieTilley\StoryBoard\debug;
use function BradieTilley\StoryBoard\error;
use BradieTilley\StoryBoard\Exceptions\InvalidMagicMethodHandlerException;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story\Action;
use BradieTilley\StoryBoard\Story\Result;
use BradieTilley\StoryBoard\Story\StoryAction;
use Closure;
use Illuminate\Support\Collection;
use Throwable;

/**
 * This object has actions, expectations and assertions
 *
 * @method static can(string|Closure|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static cannot(string|Closure|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static static can(string|Closure|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static static cannot(string|Closure|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
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
     * Current expectation
     */
    protected ?bool $can = null;

    /**
     * Flag that indicates that inheritance must halt at
     * this story in the 'family tree'. If '$can' is 'null'
     * here on this Story, we should not look any further.
     *
     * Set to true when noAssertion() is run. This will override
     * a parent can/cannot and reset it back to null for this
     * story and its children.
     */
    protected bool $canHalt = false;

    /**
     * Method alias(es) for Actions trait
     */
    public function __callActions(string $method, array $parameters): mixed
    {
        if ($method === 'can' || $method === 'cannot') {
            $method = 'set'.ucfirst($method);

            return $this->{$method}(...$parameters);
        }

        throw StoryBoardException::invalidMagicMethodHandlerException($method, InvalidMagicMethodHandlerException::TYPE_METHOD);
    }

    /**
     * Static method alias(es) for Actions trait
     */
    public static function __callStaticActions(string $method, array $parameters): mixed
    {
        if ($method === 'can' || $method === 'cannot') {
            return static::make()->{$method}(...$parameters);
        }

        throw StoryBoardException::invalidMagicMethodHandlerException($method, InvalidMagicMethodHandlerException::TYPE_STATIC_METHOD);
    }

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

    public function assert(Closure $can = null, Closure $cannot = null): static
    {
        $this->setCallback('can', $can);
        $this->setCallback('cannot', $cannot);

        return $this;
    }

    public function noAssertion(): static
    {
        $this->can = null;
        $this->canHalt = true;

        return $this;
    }

    /**
     * Specify that you expect that this task 'can run' or 'will pass'
     *
     * The name and callback can be passed in in either order.
     */
    public function setCan(string|Closure|null $name = null, string|Closure|null $callback = null): static
    {
        if (is_string($name)) {
            $this->name($name);
        } elseif (is_string($callback)) {
            $this->name($callback);
        }

        if ($name instanceof Closure) {
            $this->setCallback('can', $name);
        } elseif ($callback instanceof Closure) {
            $this->setCallback('can', $callback);
        }

        $this->can = true;

        return $this;
    }

    /**
     * Specify that you expect that this task 'cannot run' or 'will fail'
     *
     * The name and callback can be passed in in either order.
     */
    public function setCannot(string|Closure|null $name = null, string|Closure|null $callback = null): static
    {
        if (is_string($name)) {
            $this->name($name);
        } elseif (is_string($callback)) {
            $this->name($callback);
        }

        if ($name instanceof Closure) {
            $this->setCallback('cannot', $name);
        } elseif ($callback instanceof Closure) {
            $this->setCallback('cannot', $callback);
        }

        $this->can = false;

        return $this;
    }

    /**
     * Get the 'can' / 'cannot' flag for this story
     */
    public function itCan(): ?bool
    {
        return $this->can;
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

        if ($this->can === null) {
            throw StoryBoardException::expectationNotSpecified($this);
        }

        $callback = $this->can ? 'can' : 'cannot';

        if (! $this->hasCallback($callback)) {
            throw StoryBoardException::assertionCheckerNotSpecified($this);
        }

        try {
            $args = array_replace($this->getParameters(), [
                'result' => $this->getResult()->getValue(),
            ]);

            $this->runCallback($callback, $args);
        } catch (Throwable $e) {
            $this->getResult()->setError($e);

            throw $e;
        }

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

    /**
     * Inherit assertions from ancestors
     */
    public function inheritAssertions(): void
    {
        $can = $this->inheritPropertyBool('can');

        if ($can !== null) {
            $this->can = $can;
        }
    }
}
