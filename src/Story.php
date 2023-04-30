<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Concerns\ProxiesData;
use BradieTilley\Stories\Concerns\Reposes;
use BradieTilley\Stories\PendingCalls\PendingActionCall;
use BradieTilley\Stories\Repositories\DataRepository;
use BradieTilley\Stories\Repositories\Dataset;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Traits\Conditionable;
use Pest\Expectations\HigherOrderExpectation;
use Pest\TestSuite;
use PHPUnit\Framework\TestCase;

class Story
{
    use Conditionable;
    use ProxiesData;
    use Reposes;

    protected static ?Story $instance = null;

    /**
     * @var array<int, Action>
     */
    protected array $actions = [];

    public DataRepository $data;

    protected ?Dataset $dataset = null;

    public function __construct()
    {
        $this->data = new DataRepository();
    }

    /**
     * (Internal function) Set the current story that is being invoked
     */
    public static function setInstance(?Story $story): void
    {
        static::$instance = $story;
    }

    /**
     * Get the current story instance that's being invoked
     */
    public static function getInstance(): ?Story
    {
        return static::$instance;
    }

    /**
     * (Internal function) use this story as the current instance
     */
    public function use(): static
    {
        self::setInstance($this);

        return $this;
    }

    /**
     * Add an action to this story
     *
     * @param  array<string, mixed>  $arguments
     */
    public function action(string|Closure|Action|PendingActionCall $action, array $arguments = [], string $variable = null, bool $dataset = false): static
    {
        $action = Action::parse($action);
        $action = Action::resolve($action);

        $action = $action->fresh($this);

        if ($dataset) {
            $action->dataset();
        }

        $action->run($this, arguments: $arguments, variable: $variable);

        return $this;
    }

    /**
     * Add an expectation to this story
     */
    public function expects(string|Closure $expect): HigherOrderExpectation
    {
        if (is_string($expect)) {
            /** @phpstan-ignore-next-line */
            return expect($this)->get($expect);
        }

        /** @phpstan-ignore-next-line */
        return expect($this)->call($expect);
    }

    /**
     * Get the dataset variables for this story
     */
    public function dataset(): Dataset
    {
        if ($this->dataset === null) {
            /** @var array<int, mixed> $dataset */
            $dataset = $this->getTest()->providedData();

            $this->dataset ??= new Dataset($dataset);
        }

        return $this->dataset;
    }

    /**
     * Call the given callback with dependency injection
     *
     * @param  array<string, mixed>  $additional
     */
    public function call(callable $callback = null, array $additional = []): mixed
    {
        if ($callback === null) {
            return null;
        }

        return Container::getInstance()->call($callback, $this->getCallbackArguments($additional));
    }

    /**
     * Get a list of arguments that may be injected into Closure callbacks
     *
     * @param  array<mixed>  $additional
     * @return array<mixed>
     */
    public function getCallbackArguments(array $additional = []): array
    {
        $arguments = array_replace(
            [
                'story' => $this,
                'test' => $this->getTest(),
            ],
            $this->dataset()->all(),
            $this->all(),
            $additional,
        );

        return $arguments;
    }

    /**
     * Get the test case
     */
    public function getTest(): TestCase
    {
        /** @phpstan-ignore-next-line */
        return TestSuite::getInstance()->test;
    }
}
