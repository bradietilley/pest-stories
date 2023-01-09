<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;
use BradieTilley\StoryBoard\Story\Result;
use Closure;
use Illuminate\Support\Collection;
use Throwable;
use BradieTilley\StoryBoard\Story\StoryAction;

trait HasActions
{
    protected ?Result $result = null;

    /**
     * All actions and their arguments (excluding inheritance until story is registered)
     *
     * @var array<string,StoryAction>
     */
    protected array $actions = [];

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
     * Alias for setAction()
     *
     * @return $this
     */
    public function action(string|Closure|Action $action, array $arguments = [], int $order = null): self
    {
        return $this->setAction($action, $arguments, $order);
    }
    /**
     * @return $this
     */
    public function before(?Closure $before): self
    {
        /** @var HasTasks|HasCallbacks $this */
        return $this->setCallback('before', $before);
    }

    /**
     * @return $this
     */
    public function after(?Closure $after): self
    {
        /** @var HasTasks|HasCallbacks $this */
        return $this->setCallback('after', $after);
    }

    /**
     * Register a single action for this story.
     * Optionally pass in arguments (matched by name) if the action supports them.
r
     * @return $this
     */
    public function setAction(string|Closure|Action $action, array $arguments = [], int $order = null): self
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
     * Alias for setActions()
     *
     * @return $this
     */
    public function actions(iterable $actions): self
    {
        return $this->setActions($actions);
    }

    /**
     * Register multiple actions for this story.
     * 
     * The order of each action is inherited from the actions themselves.
     *
     * @return $this
     */
    public function setActions(iterable $actions): self
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
     * @requires HasInheritance
     *
     * @return array<string,StoryAction>
     */
    public function allActions(): array
    {
        $all = [];

        /** @var HasInheritance $this */
        foreach (array_reverse($this->getAncestors()) as $ancestor) {
            foreach ($ancestor->getActions() as $name => $storyAction) {
                $all[$name] = (clone $storyAction)->withStory($this);
            }
        }

        return $all;
    }

    /**
     * Inherit all actions from this story's parent
     */
    public function inheritActions(): void
    {
        $this->actions = $this->allActions();
    }

    /**
     * Resolve all actions that are inherited
     *
     * @return $this
     */
    public function registerActions(): self
    {
        /** @var Story $this */
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
     *
     * @requires HasInheritance
     *
     * @return $this
     */
    public function bootActions(): self
    {
        /** @var Story $this */

        if (empty($this->actions)) {
            throw StoryBoardException::actionNotSpecified($this);
        }

        try {
            $result = $this->getResult();
            $resultData = [
                'result' => $result->getValue(),
            ];

            $this->runCallback('before', $this->getParameters($resultData));

            /** @var Story $this */
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
            $result->setError($e);

            throw $e;
        }

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
     * @return $this
     */
    public function assert(Closure $can = null, Closure $cannot = null): self
    {
        /** @var HasCallbacks|HasTasks $this */
        $this->setCallback('can', $can);
        $this->setCallback('cannot', $cannot);

        return $this;
    }

    /**
     * @return $this
     */
    public function noAssertion(): self
    {
        $this->can = null;
        $this->canHalt = true;

        return $this;
    }

    /**
     * Set whether this task can run (i.e. passes)
     *
     * @return $this
     */
    public function can(bool $can = true): self
    {
        $this->can = $can;

        return $this;
    }

    /**
     * Set that this task cannot run (i.e. fails)
     *
     * @return $this
     */
    public function cannot(): self
    {
        return $this->can(false);
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
     *
     * @requires Story
     *
     * @return $this
     */
    public function perform(): self
    {
        /** @var Story $this */
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
            throw StoryBoardException::assertionNotFound($this);
        }

        $callback = $this->can ? 'can' : 'cannot';

        if (! $this->hasCallback($callback)) {
            throw StoryBoardException::assertionCheckerNotFound($this);
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
}