<?php

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasOrder;
use Closure;
use Illuminate\Container\Container;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Traits\HasContainer;
use Illuminate\Support\Str;

class Scenario
{
    use HasName;
    use HasOrder;
    use HasContainer;

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
     * Is this registered?
     */
    public function registered(): bool
    {
        return isset(static::$registered[$this->name]);
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
        $arguments = array_replace($story->getParameters(), $arguments);

        return $this->call($this->generator, $arguments);
    }

    /**
     * Get the name of the scenario to be referenced when building a story's set of scenarios
     */
    public static function prepare(string|Closure|self $scenario): string
    {
        if (is_string($scenario)) {
            return $scenario;
        }

        if ($scenario instanceof Closure) {
            $scenario = self::make(
                name: 'inline_' . (string) Str::random(),
                generator: $scenario,
            );
        }

        // Don't re-register if already registered, to prevent overwriting existing scenario (in the event of duplicate names)
        if (! $scenario->registered()) {
            $scenario->register();
        }

        return $scenario->getName();
    }
}