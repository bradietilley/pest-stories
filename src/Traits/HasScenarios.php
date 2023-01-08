<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Story\Scenario;
use Closure;
use Illuminate\Support\Collection;

trait HasScenarios
{
    /**
     * All scenarios and their arguments (excluding inheritance until story is registered)
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
     * Alias for setScenarios()
     *
     * @return $this
     */
    public function scenarios(iterable $scenarios): self
    {
        return $this->setScenarios($scenarios);
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
        $all = [];

        /** @var HasInheritance $this */
        foreach (array_reverse($this->getAncestors()) as $ancestor) {
            foreach ($ancestor->getScenarios() as $name => $data) {
                $all[$name] = $data;
            }
        }

        return $all;
    }

    /**
     * Inherit all scenarios from this story's parent
     */
    public function inheritScenarios(): void
    {
        $this->scenarios = $this->allScenarios();
    }

    /**
     * Resolve all scenarios that are inherited
     *
     * @return $this
     */
    public function registerScenarios(): self
    {
        /** @var Story $this */
        $this->scenarios = Collection::make($this->scenarios)
            ->sortBy(fn (array $data) => $data['scenario']->getOrder())
            ->all();

        foreach ($this->scenarios as $data) {
            /** @var Scenario $scenario */
            $scenario = $data['scenario'];
            /** @var array $args */
            $args = $data['arguments'];

            $scenario->register($this, $args);
        }

        return $this;
    }

    /**
     * Boot all registered scenarios for this test.
     *
     * @requires HasInheritance
     *
     * @return $this
     */
    public function bootScenarios(): self
    {
        /** @var HasData|HasScenarios|HasName $this */
        foreach ($this->scenarios as $data) {
            /** @var HasData|HasScenarios|HasName $this */

            /** @var Scenario $scenario */
            $scenario = $data['scenario'];
            /** @var array $args */
            $args = $data['arguments'];

            $value = $scenario->boot($this, $args);

            $this->setData($scenario->getVariable(), $value);
        }

        return $this;
    }

    /**
     * Get all names from all registered scenarios
     */
    public function getNameFromScenarios(): ?string
    {
        // Just this level
        $scenarios = Collection::make($this->scenarios)
            ->pluck('scenario')
            ->map(fn (Scenario $scenario) => $scenario->getAppendName())
            ->filter();

        return $scenarios->isNotEmpty() ? $scenarios->implode(' ') : null;
    }
}
