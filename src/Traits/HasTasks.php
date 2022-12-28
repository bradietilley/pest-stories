<?php


namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Result;
use BradieTilley\StoryBoard\Story\Task;
use Closure;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use InvalidArgumentException;

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
     * @return $this 
     */
    public function task(Closure|Task|string $task): self
    {
        $task = Task::prepare($task);

        if (! in_array($task, $this->tasks)) {
            $this->tasks[] = $task;
        }

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
     * @return $this 
     */
    public function after(Closure $after): self
    {
        $this->after = $after;
        
        return $this;
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
     */
    public function allTasks(): array
    {
        /** @var Story|self $this */
        return $this->combineFromParents('getTasks');
    }

    /**
     * @return $this
     */
    public function bootTask(): self
    {
        /** @var Story|self $this */
        $tasks = $this->allTasks();

        if (empty($tasks)) {
            throw StoryBoardException::taskNotSpecified($this);
        }

        $tasks = Collection::make($tasks)
            ->map(
                fn (string $task) => Task::fetch($task),
            )
            ->sortBy(
                fn (Task $task) => $task->getOrder(),
            )
            ->all();
        /**
         * @var array<Task> $tasks
         */

        $app = Container::getInstance();
        $result = new Result();

        try {
            $data = $this->getParameters();

            /* Call before listener */
            if ($callback = $this->before) {
                $app->call($callback, $data);
            }

            // Allow callbacks to read the `$result`
            $data['result'] = $result->getValue();

            /* Run task */
            foreach ($tasks as $task) {
                $result->setValue($task->boot($this, $data));
            }

            /* Call after listener */
            if ($callback = $this->after) {
                $app->call($callback, $data);
            }
        } catch (\Throwable $e) {
            $result->setError($e);

            throw $e;
        }

        $this->result = $result;

        $this->can = $this->inheritFromParents('getCan');
        $this->canAssertion = $this->inheritFromParents('getCanAssertion');
        $this->cannotAssertion = $this->inheritFromParents('getCannotAssertion');

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
     */
    public function assert(): void
    {
        $this->can = $this->inheritFromParents('getCan');
        
        if ($this->can === null) {
            throw StoryBoardException::assertionNotFound($this);
        }
        
        $checker = $this->can ? $this->canAssertion : $this->cannotAssertion;

        if ($checker === null) {
            throw StoryBoardException::assertionCheckerNotFound($this);
        }

        Container::getInstance()->call($checker, $this->getParameters());
    }
}