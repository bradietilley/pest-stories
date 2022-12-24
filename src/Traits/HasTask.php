<?php


namespace BradieTilley\StoryBoard\Traits;

use Closure;

trait HasTask
{
    protected array $result = [];

    protected ?bool $expectCan = null;

    /**
     * Run the task
     */
    public function run(Closure $generator): static
    {
        $result = [];

        try {
            $result['result'] = app()->call($generator, array_replace($this->allData(), [
                'story' => $this,
                'user' => $this->user(),
            ]));
        } catch (\Throwable $e) {
            $result['error'] = $e;
        }

        $this->result = $result;

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
}