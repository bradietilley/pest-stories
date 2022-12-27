<?php


namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story;
use Closure;
use Illuminate\Container\Container;

trait HasTask
{
    protected array $result = [];
    
    protected ?Closure $task = null;
    
    protected ?Closure $checkCan = null;
    
    protected ?Closure $checkCannot = null;
    
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
            throw new \Exception('No task found for story');
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

        return $this;
    }

    /**
     * @return $this 
     */
    public function check(Closure $can = null, Closure $cannot = null): self
    {
        $this->checkCan = $can;
        $this->checkCannot = $cannot;

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
     * @return $this
     */
    public function can(bool $can = true): self
    {
        $this->can = $can;
        
        return $this;
    }

    /**
     * Get the 'can' or 'cannot' flag for this story
     */
    public function getCan(): ?bool
    {
        return $this->can;
    }

    /**
     * @return $this
     */
    public function cannot(): self
    {
        return $this->can(false);
    }

    public function getCheckCan(): ?Closure
    {
        return $this->checkCan;
    }

    public function getCheckCannot(): ?Closure
    {
        return $this->checkCannot;
    }

    public function assert(): void
    {
        if ($this->can === null) {
            throw new \Exception('No expected result');
        }

        /** @var Story|self $this */
        $checker = null;
        $story = $this;

        while ($checker === null) {
            $checker = $this->can ? $story->getCheckCan() : $story->getCheckCannot();

            if ($checker !== null) {
                break;
            }

            $story = $story->getParent();

            if ($story === null) {
                break;
            }
        }

        if ($checker === null) {
            throw new \Exception('No checker');
        }

        Container::getInstance()->call($checker, $this->getParameters());
    }
}