<?php

namespace BradieTilley\StoryBoard\Traits;

trait HasScenarios
{
    protected array $scenarios = [];

    /**
     * Register a scenario for this story

     * @return $this 
     */
    public function scenario(string $name, ...$arguments): self
    {
        $this->scenarios[$name] = $arguments;

        return $this;
    }
}