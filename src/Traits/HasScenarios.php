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

    public function scenarios(): array
    {
        return $this->scenarios;
    }

    /**
     * Get all scenarios for this story, including those inherited from parents
     * 
     * @return array<string,array> 
     */
    public function getScenarios(): array
    {
        /** @var Story $this */
        return $this->combineFromParents('scenarios');
    }

    /**
     * Boot all registered scenarios for this test.
     */
    public function bootScenarios(): void
    {
        /** @var Story|self $this */
        $scenarios = $this->getScenarios();

        Collection::make($scenarios)
            ->map(fn (array $arguments, string $scenario) => [
                'scenario' => Scenario::fetch($scenario),
                'arguments' => $arguments,
            ])
            ->sortBy(fn (array $data) => $data['scenario']->getOrder())
            ->map(function (array $data) {
                /** @var Story|self $this */
                
                /** @var Scenario $scenario */
                $scenario = $data['scenario'];
                /** @var array $args */
                $args = $data['arguments'];

                $value = $scenario->boot($this, $args);

                $this->setData($scenario->variable(), $value);
            });
    }
}