<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Helpers\CallbackRepository;
use BradieTilley\Stories\Helpers\StoryAliases;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

/**
 * @method static static make(string $name = '', Closure $callback, array $arguments = [])
 */
abstract class Callback
{
    /** @var array<Closure> */
    protected array $before = [];

    /** @var array<Closure> */
    protected array $after = [];

    /** @var array<mixed> */
    protected array $with = [];

    /** The name of the variable where the callback result goes to for the invoking story */
    protected string $variable;

    public function __construct(protected string $name, protected ?Closure $callback = null, array $arguments = [])
    {
        $this->variable = $name;
        $this->with($arguments)->store();
    }

    /**
     * Store this callback class in the repository so it can be retrieved by
     * its name from anywhere.
     */
    public function store(): static
    {
        CallbackRepository::store(static::getAliasKey(), $this);

        return $this;
    }

    /**
     * Fetch the current class (action, assertion or story) with with the
     * given name
     */
    public static function fetch(string $name): static
    {
        /** @var static $callback */
        $callback = CallbackRepository::fetch(static::getAliasKey(), $name);

        return $callback;
    }

    /**
     * Fetch, create or return the given callback
     */
    public static function prepare(string|self|Closure $item): static
    {
        if ($item instanceof self) {
            /** @phpstan-ignore-next-line */
            return $item;
        }

        if (is_string($item)) {
            return static::fetch($item);
        }

        return static::make(Str::random(8), $item);
    }

    /**
     * Set the underlying callback
     */
    public function as(Closure $callback = null): static
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Set the name of this callback, action, assertion or story
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name of this callback, action, assertion or story
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the key used to find the aliased class
     */
    abstract public static function getAliasKey(): string;

    /**
     * Make a new instance of this class
     */
    public static function make(): static
    {
        $class = match (static::getAliasKey()) {
            'action' => Action::class,
            'assertion' => Assertion::class,
            'story' => Story::class,
            default => static::class,
        };

        $class = StoryAliases::getClassAlias($class);

        /** @var static $callback */
        $callback = new $class(...func_get_args());

        return $callback;
    }

    /**
     * Add some arguments to use in the callback
     */
    public function with(array $arguments = []): static
    {
        $this->with = array_replace($this->with, $arguments);

        return $this;
    }

    /**
     * Set an argument or variable to use in the callback
     */
    public function set(string $key, mixed $value): static
    {
        $this->with[$key] = $value;

        return $this;
    }

    /**
     * Get a previously set argument or variable
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->with[$key] ?? $default;
    }

    /**
     * Add a closure callback to run before this callback class is run
     */
    public function before(Closure $callback): static
    {
        $this->before[] = $callback;

        return $this;
    }

    /**
     * Get all before callbacks
     *
     * @return array<Closure>
     */
    public function getBeforeCallbacks(): array
    {
        return $this->before;
    }

    /**
     * Run any listeners for the 'before' callback
     */
    protected function runBefore(array $arguments = []): void
    {
        foreach ($this->before as $callback) {
            $this->internalCall($callback, $arguments);
        }
    }

    /**
     * Add a closure callback to run after this callback class is run
     */
    public function after(Closure $callback): static
    {
        $this->after[] = $callback;

        return $this;
    }

    /**
     * Get all after callbacks
     *
     * @return array<Closure>
     */
    public function getAfterCallbacks(): array
    {
        return $this->after;
    }

    /**
     * Run any listeners for the 'after' callback
     */
    protected function runAfter(array $arguments = []): void
    {
        foreach ($this->after as $callback) {
            $this->internalCall($callback, $arguments);
        }
    }

    /**
     * Boot this callback.
     *
     * - Run 'before' callbacks
     * - Run underlying callback
     * - Run 'after' callbacks
     */
    public function boot(array $arguments = []): mixed
    {
        $this->runBefore();

        $result = null;

        if ($this->callback !== null) {
            $result = $this->internalCall($this->callback, $arguments);
        }

        $this->runAfter([
            'result' => $result,
        ]);

        return $result;
    }

    /**
     * Internal:
     *
     * Run the given closure with the pre-registered arguments as well as the
     * given arguments.
     */
    protected function internalCall(Closure $callback, array $arguments = []): mixed
    {
        $arguments = array_replace(
            $this->with,
            $arguments,
            [
                static::getAliasKey() => $this,
            ],
        );

        return Container::getInstance()->call($callback, $arguments);
    }

    /**
     * Get the underlying primary callback, if supplied.
     */
    public function getCallback(): ?Closure
    {
        return $this->callback;
    }

    /**
     * Set the variable for this callback value to be assigned to
     */
    public function for(string $variable): static
    {
        $this->variable = $variable;

        return $this;
    }

    /**
     * Get the name of the variable for this callback value to be assigned to
     */
    public function getVariable(): string
    {
        return $this->variable;
    }
}
