<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story\Scenario;
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
     * Alias for setScenario()
     * 
     * @return $this 
     */
    public function scenario(string $name, array $arguments = []): self
    {
        return $this->setScenario($name, $arguments);
    }

    /**
     * Register a single scenario for this story.
     * Optionally pass in arguments (matched by name) if the scenario supports them.
r
     * @return $this 
     */
    public function setScenario(string $name, array $arguments = []): self
    {
        $this->scenarios[$name] = $arguments;

        return $this;
    }

    /**
     * Register multiple scenarios for this story
     * 
     * @return $this
     */
    public function setScenarios(iterable $scenarios): self
    {
        foreach ($scenarios as $name => $arguments) {
            $this->setScenario($name, $arguments);
        }

        return $this;
    }

    /**
     * Get a registered scenario's arguments (no inheritance lookup)
     */
    public function getScenario(string $name): ?array
    {
        return $this->scenarios[$name] ?? null;
    }

    /**
     * Get all regsitered scenarios for this story (no inheritance lookup)
     */
    public function getScenarios(): array
    {
        return $this->scenarios;
    }

    /**
     * Get all scenarios for this story, including those inherited from parents
     * 
     * @return array<string,array> 
     */
    public function allScenarios(): array
    {
        /** @var Story $this */
        return $this->combineFromParents('getScenarios');
    }

    /**
     * Boot all registered scenarios for this test.
     */
    public function bootScenarios(): void
    {
        /** @var Story|self $this */
        $scenarios = $this->allScenarios();

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