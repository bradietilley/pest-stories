<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Exceptions\ExpectationChainStoryRequiredException;
use BradieTilley\Stories\Exceptions\FunctionAliasNotFoundException;
use BradieTilley\Stories\Helpers\StoryAliases;
use BradieTilley\Stories\Traits\ExpectationCallProxies;
use Closure;

class ExpectationChain
{
    use ExpectationCallProxies;

    protected ?Story $story = null;

    public InvocationQueue $queue;

    public function __construct()
    {
        $this->queue = InvocationQueue::make();
    }

    /**
     * @param  string  $name
     * @param  array  $arguments
     */
    public function __call($name, $arguments): static
    {
        return $this->registerExpectationMethod($name, $arguments);
    }

    /**
     * @param  string  $name
     */
    public function __get($name): static
    {
        return $this->registerExpectationProperty($name);
    }

    public function registerExpectationMethod(string $name, array $arguments): static
    {
        $this->queue->push(
            Invocation::makeMethod(name: $name, arguments: $arguments),
        );

        return $this;
    }

    public function registerExpectationProperty(string $name): static
    {
        $this->queue->push(
            Invocation::makeProperty(name: $name),
        );

        return $this;
    }

    public function registerExpectationValue(string|Closure $value): static
    {
        $function = StoryAliases::getFunction('expect');

        if (! function_exists($function)) {
            // @codeCoverageIgnoreStart
            throw FunctionAliasNotFoundException::make('expect', $function);
            // @codeCoverageIgnoreEnd
        }

        $this->queue->push(
            Invocation::makeFunction(name: $function, arguments: [$value])
        );

        return $this;
    }

    /**
     * Finish chaining expectation logic and return to chaining story logic
     */
    public function setStory(Story $story): static
    {
        $this->story = $story;

        return $this;
    }

    /**
     * Finish chaining expectation logic and return to chaining story logic
     */
    public function story(): Story
    {
        if ($this->story === null) {
            throw ExpectationChainStoryRequiredException::make();
        }

        return $this->story;
    }

    /**
     * Create a new chainable expectation
     */
    public function and(string|Closure $newValue): static
    {
        return $this->registerExpectationValue($newValue);
    }

    /**
     * Create a new chainable expectation
     */
    public function expect(string|Closure $newValue): static
    {
        return $this->and($newValue);
    }

    /**
     * Add a child story to run when this story runs
     *
     * @param  array<string|Story>|string|Story  $story
     */
    public function stories(array|string|Story|ExpectationChain $story, array $arguments = []): Story
    {
        return $this->story()->stories($story, $arguments);
    }

    /**
     * Make a new Expectation Chain
     */
    public static function make(): static
    {
        $class = StoryAliases::getClassAlias(ExpectationChain::class);

        /** @var static $class */
        $class = new $class(...func_get_args());

        return $class;
    }

    /**
     *Inherit the chain from the given parent
     */
    public function inherit(ExpectationChain $parent): void
    {
        $queue = $parent->queue->items;

        foreach ($this->queue->items as $invocation) {
            $queue[] = $invocation;
        }

        $this->queue->items = $queue;
    }
}
