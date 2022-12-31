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

    protected static array $stored = [];

    protected ?Closure $registeringCallback = null;

    protected ?Closure $bootingCallback = null;

    public function __construct(
        protected string $name,
        protected ?Closure $generator = null,
        protected int $order,
    ) {}

    /**
     * Set the generator
     * 
     * @return $this 
     */
    public function as(Closure $generator): self
    {
        $this->generator = $generator;

        return $this;
    }

    /**
     * Set a callback when this action is registering
     * 
     * @return $this 
     */
    public function registering(Closure $callback): self
    {
        $this->registeringCallback = $callback;

        return $this;
    }

    /**
     * Set a callback when this action is booting
     * 
     * @return $this 
     */
    public function booting(Closure $callback): self
    {
        $this->bootingCallback = $callback;

        return $this;
    }

    /**
     * Manually register the action (if not created via `make()`)
     *
     * @return $this
     */
    public function store(): static
    {
        static::$stored[static::class] ??= [];
        static::$stored[static::class][$this->name] = $this;

        return $this;
    }

    /**
     * Is this stored?
     */
    public function stored(): bool
    {
        static::$stored[static::class] ??= [];

        return isset(static::$stored[static::class][$this->name]);
    }

    /**
     * Flush all registrations
     */
    public static function flush(): void
    {
        if (static::class === AbstractAction::class) {
            static::$stored = [];

            return;
        }

        static::$stored[static::class] = [];
    }

    /**
     * Get an exception for scenario/task not found
     */
    abstract protected static function notFound(string $name): StoryBoardException;

    /**
     * Get an exception for generator not found
     */
    abstract protected static function generatorNotFound(string $name): StoryBoardException;

    /**
     * Fetch a action from the registrar
     * 
     * @throws StoryBoardException
     * @return static
     */
    public static function fetch(string $name): static
    {
        static::$stored[static::class] ??= [];

        if (! isset(static::$stored[static::class][$name])) {
            throw static::notFound($name);
        }

        return static::$stored[static::class][$name];
    }

    /**
     * Register this action for the given story.
     * By default nothing is done.
     */
    public function register(Story $story, array $arguments = []): void
    {
        $this->callOptional($this->registeringCallback, $story->getParameters($arguments));
    }

    /**
     * Boot this action for the given story
     * 
     * @throws StoryBoardException
     */
    public function boot(Story $story, array $arguments = []): mixed
    {
        $this->callOptional($this->bootingCallback, $story->getParameters($arguments));

        if ($this->generator === null) {
            throw static::generatorNotFound($this->getName());
        }

        return $this->call($this->generator, $story->getParameters($arguments));
    }

    /**
     * Make and register this action
     *
     * @return $this
     */
    public static function make()
    {
        return (new static(...func_get_args()))->store();
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
        if (! $action->stored()) {
            $action->store();
        }

        return $action;
    }
}
