<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story\Scenario;
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
        $scenario = Scenario::prepare($scenario);

        $this->scenarios[$scenario->getName()] = [
            'scenario' => $scenario,
            'arguments' => $arguments,
        ];

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
        /** @var HasData|HasScenarios|HasName $this */

        Collection::make($this->allScenarios())
            ->sortBy(fn (array $data) => $data['scenario']->getOrder())
            ->map(function (array $data) {
                /** @var HasData|HasScenarios|HasName $this */

                /** @var Scenario $scenario */
                $scenario = $data['scenario'];
                /** @var array $args */
                $args = $data['arguments'];

                $value = $scenario->boot($this, $args);

                $this->setData($scenario->getVariable(), $value);
            });
    }

    public function getNameFromScenarios(): ?string
    {
        // Just this level
        $scenarios = Collection::make($this->getScenarios())
            ->pluck('scenario')
            ->map(fn (Scenario $scenario) => $scenario->getAppendName())
            ->filter();

        return $scenarios->isNotEmpty() ? $scenarios->implode(' ') : null;
    }
}
