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

    protected ?bool $expectCan = null;

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

    public function bootTask(): self
    {
        $task = $this->getTask();

        if ($task === null) {
            throw new \Exception('No task found for story');
        }

        $app = Container::getInstance();
        $result = [];

        try {
            $data = array_replace($this->allData(), [
                'story' => $this,
                'user' => $this->user(),
            ]);

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

        return $this;
    }

    public function check(Closure $can = null, Closure $cannot = null): self
    {
        $this->checkCan = $can;
        $this->checkCannot = $cannot;

        return $this;
    }

    public function noAssertion(): self
    {
        $this->expectCan = null;

        return $this;
    }

    public function can(bool $can = true): self
    {
        $this->expectCan = $can;
        
        return $this;
    }

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
        if ($this->expectCan === null) {
            throw new \Exception('No expected result');
        }

        /** @var Story|self $this */
        $checker = null;
        $story = $this;

        while ($checker === null) {
            $checker = $this->expectCan ? $story->getCheckCan() : $story->getCheckCannot();

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

        $container = Container::getInstance();

        $container->call($checker, [
            'story' => $this,
            'can' => $this->checkCan,
        ]);
    }
}