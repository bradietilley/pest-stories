<?php

namespace BradieTilley\StoryBoard;

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
        protected int $order = 0,
    ) {
        $this->setOrder($order);
    }

    /**
     * @return $this
     */
    public function register(): self
    {
        static::$registered[$this->name] = $this;

        return $this;
    }

    /**
     * Fetch a scenario
     */
    public static function fetch(string $name): Scenario
    {
        return static::$registered[$name];
    }

    /**
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