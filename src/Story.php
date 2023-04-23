<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Pest\Expectation;
use Pest\Expectations\HigherOrderExpectation;
use Pest\TestSuite;

class Story
{
    /**
     * @var array<int, Action>
     */
    protected array $actions = [];

    /**
     * @var array<mixed>
     */
    protected array $data = [];

    /**
     * Add an action to this story
     */
    public function action(string|Closure|Action $action, array $arguments = [], string $variable = null): static
    {
        $action = Action::parse($action);

        $action->fresh($this)->run($this, arguments: $arguments, variable: $variable);

        return $this;
    }

    /**
     * Add an expectation to this story
     */
    public function expects(string|Closure $expect): HigherOrderExpectation
    {
        if (is_string($expect)) {
            /** @phpstan-ignore-next-line */
            return expect($this)->getData($expect);
        }

        /** @phpstan-ignore-next-line */
        return expect($this)->callCallback($expect);
    }

    /**
     * Get a shared variable in this story
     */
    public function getData(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * Set a shared variable in this story
     */
    public function setData(string $key, mixed $value): static
    {
        Arr::set($this->data, $key, $value);

        return $this;
    }

    /**
     * Check the existence of a shared variable in this story
     */
    public function hasData(string $key): bool
    {
        return Arr::has($this->data, $key);
    }

    /**
     * Get all shared variables in this story
     */
    public function allData(): array
    {
        return $this->data;
    }

    /**
     * Call the given callback with dependency injection
     */
    public function callCallback(callable $callback = null, array $additional = []): mixed
    {
        if ($callback === null) {
            return null;
        }

        return Container::getInstance()->call($callback, $this->getCallbackArguments($additional));
    }

    /**
     * Get a list of arguments that may be injected into Closure callbacks
     */
    public function getCallbackArguments(array $additional = []): array
    {
        $arguments = array_replace(
            [
                'story' => $this,
                'test' => TestSuite::getInstance()->test,
            ],
            $this->allData(),
            $additional,
        );

        return $arguments;
    }
}
