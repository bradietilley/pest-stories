<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Scenario;
use BradieTilley\StoryBoard\Story;
use Illuminate\Support\Collection;

trait HasScenarios
{
    protected array $scenarios = [];

    /**
     * Register a scenario for this story

     * @return $this 
     */
    public function scenario(string $name, array $arguments = []): self
    {
        $this->scenarios[$name] = $arguments;

        return $this;
    }

    public function getScenarios(): array
    {
        /** @var self|Story $this */
        $scenarios = $this->scenarios;
        
        if ($this->hasParent()) {
            $scenarios = array_replace($this->parent->getScenarios(), $scenarios);
        }

        return $scenarios;
    }

    /**
     * @todo Add priority/boot order
     */
    public function bootScenarios(): void
    {
        /** @var Story|self $this */
        $scenarios = $this->getScenarios();

        Collection::make($scenarios)
            ->map(function (array $arguments, string $scenario) {
                /** @var Story|self $this */
                $scenario = Scenario::fetch($scenario);
                $value = $scenario->boot($this, $arguments);

                $this->setData($scenario->variable(), $value);
            });
    }
}