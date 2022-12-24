<?php

namespace BradieTilley\StoryBoard;

use BradieTilley\StoryBoard\Traits\HasName;
use Closure;

class Scenario
{
    use HasName;

    protected static array $registered = [];

    public function __construct(
        public string $name,
        public string $variable,
        public Closure $generator
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
}