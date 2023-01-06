<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Result;
use BradieTilley\StoryBoard\Story\Task;
use Closure;
use Illuminate\Support\Collection;
use Throwable;

trait HasTasks
{
    protected ?Result $result = null;

    /**
     * Tasks against this Story (excluding inheritance)
     * 
     * @var array<Task>
     */
    protected array $tasks = [];

    /**
     * Tasks against this Story (including inheritance)
     * 
     * @var array<Task>
     */
    protected array $tasksRegistered = [];

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
     * @return $this
     */
    public function task(Closure|Task|string $task): self
    {
        $task = Task::prepare($task);

        $this->tasks[$task->getName()] = $task;

        return $this;
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
     * Get this story's tasks
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * Get this story's tasks and its parents (etc) tasks
     *
     * @requires HasInheritance
     */
    public function allTasks(): array
    {
        $all = [];

        /** @var HasInheritance $this */
        foreach (array_reverse($this->getAncestors()) as $ancestor) {
            foreach ($ancestor->getTasks() as $name => $args) {
                $all[$name] = $args;
            }
        }

        return $all;
    }

    public function inheritTasks(): void
    {
        $this->tasks = $this->allTasks();
    }

    /**
     * @requires Story
     * 
     * @return $this 
     */
    public function registerTasks(): self
    {
        /** @var Story $this */

        $this->tasksRegistered = Collection::make($this->tasks)
            ->values()
            ->sortBy(fn (Task $task) => $task->getOrder())
            ->all();
        foreach ($this->tasksRegistered as $task) {
            /** @var Task $task */
            $task->register($this, $this->getParameters());
        }

        return $this;
    }

    /**
     * @requires Story
     *
     * @return $this
     */
    public function bootTasks(): self
    {
        /** @var Story $this */
        $tasks = $this->tasksRegistered;

        if (empty($tasks)) {
            throw StoryBoardException::taskNotSpecified($this);
        }
        
        $result = $this->getResult();

        try {
            $data = $this->getParameters();

            $this->runCallback('before', $data);

            /* Run task */
            foreach ($tasks as $task) {
                // Allow callbacks to read the `$result`
                $data['result'] = $result->getValue();

                $result->setValue($task->boot($this, $data));

                // Allow callbacks to read the `$result`
                $data['result'] = $result->getValue();
            }

            /* Call after listener */
            $this->runCallback('after', $data);
        } catch (Throwable $e) {
            $result->setError($e);

            throw $e;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function check(Closure $can = null, Closure $cannot = null): self
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
     * Run the assertions
     *
     * @requires Story
     */
    public function assert(): self
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
                'result' => $this->result ? $this->result->getValue() : null,
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
