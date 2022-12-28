<?php

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Traits\HasContainer;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasOrder;
use Closure;
use Illuminate\Support\Str;

abstract class AbstractAction
{
    use HasName;
    use HasOrder;
    use HasContainer;

    protected static array $registered = [];

    public function __construct(
        protected string $name,
        protected Closure $generator,
        protected int $order,
    ) {}

    /**
     * Manually register the action (if not created via `make()`)
     *
     * @return $this
     */
    public function register(): static
    {
        static::$registered[static::class] ??= [];
        static::$registered[$this->name] = $this;

        return $this;
    }

    /**
     * Is this registered?
     */
    public function registered(): bool
    {
        static::$registered[static::class] ??= [];

        return isset(static::$registered[static::class][$this->name]);
    }

    /**
     * Get an exception for story not found
     */
    abstract protected static function notFound(string $name): StoryBoardException;

    /**
     * Fetch a action from the registrar
     * 
     * @return static
     */
    public static function fetch(string $name): static
    {
        static::$registered[static::class] ??= [];

        if (! isset(static::$registered[$name])) {
            throw static::notFound($name);
        }

        return static::$registered[$name];
    }

    /**
     * Boot this action for the given story
     */
    public function boot(Story $story, array $arguments): mixed
    {
        $arguments = array_replace($story->getParameters(), $arguments);

        return $this->call($this->generator, $arguments);
    }

    /**
     * Make and register this action
     *
     * @return $this
     */
    public static function make()
    {
        return (new static(...func_get_args()))->register();
    }

    /**
     * Get the name of the action to be referenced when building a story's set of actions
     */
    public static function prepare(string|Closure|self $action): self
    {
        if (is_string($action)) {
            return self::fetch($action);
        }

        if ($action instanceof Closure) {
            $action = static::make(
                'inline_'.(string) Str::random(),
                $action,
            );
        }

        // Don't re-register if already registered, to prevent overwriting existing action (in the event of duplicate names)
        if (! $action->registered()) {
            $action->register();
        }

        return $action;
    }
}
