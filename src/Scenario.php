<?php

namespace BradieTilley\StoryBoard;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasOrder;
use Closure;
use Illuminate\Container\Container;

class Scenario
{
    use HasName;
    use HasOrder;

    protected static array $registered = [];

    public function __construct(
        protected string $name,
        protected string $variable,
        protected Closure $generator,
        protected ?int $order = null,
    ) {
        if ($order !== null) {
            $this->order($order);
        }
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
    public static function make(string $name, string $variable, Closure $generator)
    {
        return (new self($name, $variable, $generator))->register();
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