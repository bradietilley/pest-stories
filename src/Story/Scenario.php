<?php

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasOrder;
use Closure;
use Illuminate\Container\Container;
use BradieTilley\StoryBoard\Story;

class Scenario
{
    use HasName;
    use HasOrder;

    protected static array $registered = [];

    protected string $variable;

    protected int $order;

    public function __construct(
        protected string $name,
        protected Closure $generator,
        ?string $variable = null,
        ?int $order = null,
    ) {
        $this->variable = $variable ?? $name;
        $this->order($order);
    }

    /**
     * Manually register the scenario (if not created via `make()`)
     * 
     * @return $this
     */
    public function register(): self
    {
        static::$registered[$this->name] = $this;

        return $this;
    }

    /**
     * Fetch a scenario from the registrar
     */
    public static function fetch(string $name): Scenario
    {
        if (! isset(static::$registered[$name])) {
            throw StoryBoardException::scenarioNotFound($name);
        }

        return static::$registered[$name];
    }

    /**
     * Make and register this scenario
     * 
     * @return $this
     */
    public static function make(string $name, Closure $generator, ?string $variable = null, ?int $order = null)
    {
        return (new self(
            name: $name,
            generator: $generator,
            variable: $variable,
            order: $order,
        ))->register();
    }

    /**
     * Get the name of the variable
     */
    public function variable(): string
    {
        return $this->variable;
    }

    /**
     * Boot this scenario for the given story
     */
    public function boot(Story $story, array $arguments): mixed
    {
        $generator = $this->generator;
        $arguments = array_replace($story->getParameters(), $arguments);

        return Container::getInstance()->call($generator, $arguments);
    }
}