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
     * @var array<Closure>
     */
    protected array $tasks = [];

    protected ?Closure $canAssertion = null;

    protected ?Closure $cannotAssertion = null;

    protected ?Closure $before = null;

    protected ?Closure $after = null;

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
    public function before(Closure $before): self
    {
        $this->before = $before;

        return $this;
    }

    /**
     * Get the Before callback
     */
    public function getBefore(): ?Closure
    {
        return $this->before;
    }

    /**
     * @return $this
     */
    public function after(Closure $after): self
    {
        $this->after = $after;

        return $this;
    }

    /**
     * Get the After callback
     */
    public function getAfter(): ?Closure
    {
        return $this->after;
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
        /** @var HasInheritance $this */
        return $this->combineFromParents('getTasks');
    }

    /**
     * @requires Story
     *
     * @return $this
     */
    public function bootTask(): self
    {
        /** @var Story $this */
        $tasks = $this->allTasks();

        if (empty($tasks)) {
            throw StoryBoardException::taskNotSpecified($this);
        }

        $tasks = Collection::make($tasks)
            ->values()
            ->sortBy(fn (Task $task) => $task->getOrder())
            ->all();
        /**
         * @var array<Task> $tasks
         */
        $result = $this->getResult();

        $this->before = $this->inheritFromParents('getBefore');
        $this->after = $this->inheritFromParents('getAfter');
        $this->can = $this->inheritFromParents('getCan');
        $this->canAssertion = $this->inheritFromParents('getCanAssertion');
        $this->cannotAssertion = $this->inheritFromParents('getCannotAssertion');

        try {
            $data = $this->getParameters();

            /* Call before listener */
            if ($callback = $this->before) {
                $this->call($callback, $data);
            }

            /* Run task */
            foreach ($tasks as $task) {
                // Allow callbacks to read the `$result`
                $data['result'] = $result->getValue();

                $result->setValue($task->boot($this, $data));

                // Allow callbacks to read the `$result`
                $data['result'] = $result->getValue();
            }

            /* Call after listener */
            if ($callback = $this->after) {
                $this->call($callback, $data);
            }
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
        $this->canAssertion = $can;
        $this->cannotAssertion = $cannot;

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
    public function getCan(): ?bool
    {
        return $this->can;
    }

    /**
     * Get the halt flag for the 'can' property.
     */
    public function getCanHalt(): bool
    {
        return $this->canHalt;
    }

    /**
     * Get the callback that detmerines if the task passed
     * when the story is expected to pass.
     */
    public function getCanAssertion(): ?Closure
    {
        return $this->canAssertion;
    }

    /**
     * Get the callback that detmerines if the task failed
     * when the story is expected to fail.
     */
    public function getCannotAssertion(): ?Closure
    {
        return $this->cannotAssertion;
    }

    /**
     * Run the assertions
     *
     * @requires Story
     */
    public function assert(): void
    {
        /** @var Story $this */

        if ($this->skipDueToIsolation()) {
            $test = $this->getTest();

            if ($test) {
                // @codeCoverageIgnoreStart
                $test->markTestSkipped('Isolation Mode Enabled');
                // @codeCoverageIgnoreEnd
            }
            
            return;
        }

        $this->can = $this->inheritFromParents('getCan');

        if ($this->can === null) {
            throw StoryBoardException::assertionNotFound($this);
        }

        $checker = $this->can ? $this->canAssertion : $this->cannotAssertion;

        if ($checker === null) {
            throw StoryBoardException::assertionCheckerNotFound($this);
        }

        try {
            $this->call($checker, array_replace($this->getParameters(), [
                'result' => $this->result ? $this->result->getValue() : null,
            ]));
        } catch (Throwable $e) {
            $this->getResult()->setError($e);

            throw $e;
        }
    }

    /**
     * Get the result from the task(s) if already run
     */
    public function getResult(): Result
    {
        return $this->result ??= new Result();
    }
}
