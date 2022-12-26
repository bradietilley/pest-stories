<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Scenario;
use BradieTilley\StoryBoard\Story;
use Illuminate\Support\Collection;

trait HasScenarios
{
    /**
     * Scenarios and their arguments
     * 
     * @var array<string,array>
     */
    protected array $scenarios = [];

    /**
     * Register a scenario for this story.
     * Optionally pass in arguments (matched by name) if the scenario supports them.

     * @return $this 
     */
    public function scenario(string $name, array $arguments = []): self
    {
        $this->scenarios[$name] = $arguments;

        return $this;
    }

    /**
     * Get all scenarios for this story, including those inherited from parents
     * 
     * @return array<string,array> 
     */
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
     * Boot all registered scenarios for this test.
     * 
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