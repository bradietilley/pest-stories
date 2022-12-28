<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story\Scenario;
use BradieTilley\StoryBoard\Story;
use Closure;
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
    public function scenario(string|Closure|Scenario $scenario, array $arguments = []): self
    {
        return $this->setScenario($scenario, $arguments);
    }

    /**
     * Register a single scenario for this story.
     * Optionally pass in arguments (matched by name) if the scenario supports them.
r
     * @return $this 
     */
    public function setScenario(string|Closure|Scenario $scenario, array $arguments = []): self
    {
        $name = Scenario::prepare($scenario);

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
        foreach ($scenarios as $scenario => $arguments) {
            // Closures and classes will be int key
            if (is_string($arguments) || ($arguments instanceof Closure) || ($arguments instanceof Scenario)) {
                $scenario = $arguments;
                $arguments = [];
            }

            $this->setScenario($scenario, $arguments);
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
     * @requires HasInheritance
     * 
     * @return array<string,array> 
     */
    public function allScenarios(): array
    {
        /** @var HasInheritance $this */
        return $this->combineFromParents('getScenarios');
    }

    /**
     * Boot all registered scenarios for this test.
     * 
     * @requires HasInheritance
     */
    public function bootScenarios(): void
    {
        Collection::make($this->allScenarios())
            ->map(fn (array $arguments, string $scenario) => [
                'scenario' => Scenario::fetch($scenario),
                'arguments' => $arguments,
            ])
            ->sortBy(fn (array $data) => $data['scenario']->getOrder())
            ->map(function (array $data) {
                /** @var HasData|HasScenarios $this */
                
                /** @var Scenario $scenario */
                $scenario = $data['scenario'];
                /** @var array $args */
                $args = $data['arguments'];

                $value = $scenario->boot($this, $args);

                $this->setData($scenario->variable(), $value);
            });
    }
}