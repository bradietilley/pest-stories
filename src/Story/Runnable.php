<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\RunnableGeneratorNotFoundException;
use BradieTilley\StoryBoard\Exceptions\RunnableNotFoundException;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Traits\HasCallbacks;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasOrder;
use BradieTilley\StoryBoard\Traits\HasRepeater;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Runnable
{
    use HasName;
    use HasOrder;
    use HasCallbacks;
    use HasRepeater;

    protected static array $stored = [];

    protected string $variable;

    protected ?string $appendName = null;

    protected int $order;

    public function __construct(
        protected string $name,
        ?Closure $generator = null,
        ?string $variable = null,
        ?int $order = null,
    ) {
        $this->variable = $variable ?? $name;
        $this->order = $order ?? $this->getNextOrder();
        $this->setCallback('generator', $generator);
    }

    /**
     * Generate a new name when cloned
     */
    public function __clone()
    {
        $this->__cloneName();
    }

    /**
     * Clone this action into another action with the same callbacks, order and append name
     */
    public function clone(): static
    {
        return clone $this;
    }

    /**
     * Set the generator
     */
    public function as(Closure $generator): static
    {
        return $this->setCallback('generator', $generator);
    }

    /**
     * Set a callback when this action is registering
     */
    public function registering(Closure $callback): static
    {
        return $this->setCallback('register', $callback);
    }

    /**
     * Set a callback when this action is booting
     */
    public function booting(Closure $callback): static
    {
        return $this->setCallback('boot', $callback);
    }

    /**
     * Get all actions
     *
     * @return Collection<string, static>
     */
    public static function all(): Collection
    {
        /** @var array<string, static> $all */
        $all = static::$stored[static::class] ?? [];

        return Collection::make($all);
    }

    /**
     * Manually register the action (if not created via `make()`)
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
        static::$orderCounter = 0;

        if (static::class === Runnable::class) {
            static::$stored = [];

            return;
        }

        static::$stored[static::class] = [];
    }

    /**
     * Get an exception for action not found
     */
    protected static function notFound(string $name): StoryBoardException
    {
        /** @todo */
        return new RunnableNotFoundException();
    }

    /**
     * Get an exception for generator not found
     */
    protected static function generatorNotFound(string $name): StoryBoardException
    {
        /** @todo */
        return new RunnableGeneratorNotFoundException();
    }

    /**
     * Fetch a action from the registrar
     *
     *
     * @throws StoryBoardException
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
        $this->reset();

        $this->runCallback('register', $story->getParameters($arguments));
    }

    /**
     * Boot this action for the given story
     *
     * @throws StoryBoardException
     */
    public function boot(Story $story, array $arguments = []): mixed
    {
        $result = null;

        while ($this->repeating()) {
            $this->runCallback('boot', $story->getParameters($arguments));

            if (! $this->hasCallback('generator')) {
                throw static::generatorNotFound($this->getNameString());
            }

            $result = $this->runCallback('generator', $story->getParameters($arguments));
        }

        return $result;
    }

    /**
     * Get the alias for this type of action (for use in config)
     */
    public static function getAliasName(): string
    {
        return 'runnable';
    }

    /**
     * Make and register this action
     */
    public static function make(string $name, ?Closure $generator = null, ?string $variable = null, ?int $order = null): static
    {
        $class = Config::getAliasClass(static::getAliasName(), Runnable::class);

        /** @var static $action */
        $action = new $class($name, $generator, $variable, $order);

        return $action->store();
    }

    /**
     * Get the name of the action to be referenced when building a story's set of actions
     *
     * @param  string|Closure|static  $action
     */
    public static function prepare(string|Closure|self $action): static
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

    /**
     * Reset any properties modified by previous stories
     */
    public function reset(): static
    {
        $this->resetRepeater();

        return $this;
    }

    /**
     * Get the name of the variable
     */
    public function getVariable(): string
    {
        return $this->variable;
    }

    /**
     * Set the name of the variable
     */
    public function variable(string $variable): static
    {
        $this->variable = $variable;

        return $this;
    }

    /**
     * Append the name of this assertion to the tests
     */
    public function appendName(string $name = null): static
    {
        $this->appendName = $name ?? str_replace('_', ' ', $this->getNameString());

        return $this;
    }

    /**
     * Get the name to append to the test case, if any was specified
     */
    public function getAppendName(): ?string
    {
        return $this->appendName;
    }
}
