<?php

namespace BradieTilley\StoryBoard;

use BradieTilley\StoryBoard\Traits\HasName;
use Closure;
use Illuminate\Container\Container;

class Scenario
{
    use HasName;

    protected static array $registered = [];

    public function __construct(
        protected string $name,
        protected string $variable,
        protected Closure $generator,
    ) {

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

    public function boot(Story $story, $arguments): void
    {
        $generator = $this->generator;

        Container::getInstance()->call($generator, [
            'story' => $story,
        ] + $arguments);
    }
}