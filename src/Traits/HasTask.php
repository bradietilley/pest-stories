<?php


namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story;
use Closure;
use Exception;
use Illuminate\Container\Container;
use InvalidArgumentException;

trait HasTask
{
    protected array $result = [];
    
    protected ?Closure $task = null;
    
    protected ?Closure $canAssertion = null;
    
    protected ?Closure $cannotAssertion = null;
    
    protected ?Closure $before = null;
    
    protected ?Closure $after = null;

    protected ?bool $can = null;

    /**
     * @return $this 
     */
    public function task(Closure $task): self
    {
        $this->task = $task;

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

    public function getTask(): ?Closure
    {
        /** @var Story|self $story */
        $story = $this;
        
        if ($this->task !== null) {
            return $this->task;
        }

        $task = null;

        while ($task === null) {
            $story = $story->getParent();

            if ($story === null) {
                break;
            }

            if ($story->getTask() !== null) {
                return $story->getTask();
            }
        }

        return null;
    }

    /**
     * @return $this
     */
    public function bootTask(): self
    {
        /** @var Story|self $this */
        $task = $this->getTask();

        if ($task === null) {
            throw StoryBoardException::taskNotFound($this);
        }

        $app = Container::getInstance();
        $result = [];

        try {
            $data = $this->getParameters();

            /* Call before listener */
            if ($callback = $this->before) {
                $app->call($callback, $data);
            }

            /* Run task */
            $result['result'] = $app->call($task, $data);

            /* Call after listener */
            if ($callback = $this->after) {
                $data['result'] = $result['result'];

                $app->call($callback, $data);
            }
        } catch (\Throwable $e) {
            throw $e;
            $result['error'] = $e;
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