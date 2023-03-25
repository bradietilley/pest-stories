<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Exceptions\ExpectationChainStoryRequiredException;
use BradieTilley\Stories\Helpers\StoryAliases;
use BradieTilley\Stories\Traits\ExpectationCallProxies;
use Closure;

class ExpectationChain
{
    use ExpectationCallProxies;

    /**
     * Queued expectation methods
     */
    public array $chain = [];

    protected ?Story $story = null;

    public function __construct()
    {
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
        $this->chain[] = [
            'type' => 'method',
            'name' => $name,
            'args' => $arguments,
        ];

        return $this;
    }

    public function registerExpectationProperty(string $name): static
    {
        $this->chain[] = [
            'type' => 'property',
            'name' => $name,
        ];

        return $this;
    }

    public function registerExpectationValue(string|Closure $value): static
    {
        $this->chain[] = [
            'type' => 'expect',
            'value' => $value,
        ];

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

    public function and(string|Closure $newValue): static
    {
        return $this->registerExpectationValue($newValue);
    }

    /**
     * Make a new Expectation Queue
     */
    public static function make(): static
    {
        $class = StoryAliases::getClassAlias(ExpectationChain::class);

        /** @var static $class */
        $class = new $class(...func_get_args());

        return $class;
    }
}
