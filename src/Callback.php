<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Contracts\InvokableCallback;
use BradieTilley\Stories\Helpers\CallbackRepository;
use BradieTilley\Stories\Helpers\StoryAliases;
use BradieTilley\Stories\Traits\HasSequences;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

/**
 * @method static static make(string $name = null, Closure $callback, array $arguments = [])
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

    /** Repeater controller */
    protected ?Repeater $repeater = null;

    /** Time limit controller */
    protected ?Alarm $alarm = null;

    /** The name of the callback */
    protected string $name;

    /** The Primary callback */
    protected ?Closure $callback = null;

    public function __construct(string $name = null, ?Closure $callback = null, array $arguments = [])
    {
        $name ??= static::getRandomName();

        $this->setName($name)
            ->as($callback)
            ->for($name)
            ->with($arguments)
            ->store();
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

        return static::make()->as($item);
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
            default => null,
        };

        /**
         * Use aliased classes when calling `::make()` on any
         * base-level callback classes like Action, Assertion
         * or Story. If a custom child class is referenced in
         * a ::make() call then use the static class directly
         */
        if ($class === static::class) {
            $class = StoryAliases::getClassAlias($class);

            /** @var static $callback */
            $callback = new $class(...func_get_args());

            return $callback;
        }

        /** @phpstan-ignore-next-line */
        return new static(...func_get_args());
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
     * Run any listeners for the 'after' callback
     */
    protected function runAfter(array $arguments = []): void
    {
        foreach ($this->after as $callback) {
            $this->internalCall($callback, $arguments);
        }
    }

    /**
     * Run the callback including the timeout alarm
     */
    public function process(array $arguments = []): mixed
    {
        $alarm = $this->alarm();

        if ($alarm) {
            $alarm->start();
        }

        $response = $this->boot($arguments);

        if ($alarm) {
            $alarm->stop();
        }

        return $response;
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

        $arguments = array_replace($arguments, [
            'result' => null,
            'repeater' => $repeater = (clone $this->repeater())->reset(),
        ]);

        if ($this->callback !== null) {
            while ($repeater->more()) {
                $repeater->increment();

                $arguments['result'] = $this->internalCall($this->callback, $arguments);
            }
        }

        $this->runAfter($arguments);

        if (in_array(HasSequences::class, class_uses_recursive($this))) {
            /** @var static&HasSequences $this @phpstan-ignore-line */
            $this->runSequence(
                $arguments['story'] ?? $this,
                $this->getInternalCallArguments($arguments)
            );
        }

        if ($this instanceof InvokableCallback) {
            $allArguments = $this->getInternalCallArguments($arguments);

            $arguments['result'] = Container::getInstance()->call([$this, '__invoke'], $allArguments);
        }

        return $arguments['result'];
    }

    /**
     * Get all arguments to pass to the Laravel Container when
     * calling any callback
     */
    protected function getInternalCallArguments(array $arguments): array
    {
        return array_replace(
            $this->with,
            $arguments,
            [
                static::getAliasKey() => $this,
            ],
        );
    }

    /**
     * Internal:
     *
     * Run the given closure with the pre-registered arguments as well as the
     * given arguments.
     */
    public function internalCall(Closure $callback, array $arguments = []): mixed
    {
        $arguments = $this->getInternalCallArguments($arguments);

        /** @var Closure $callback */
        $callback = Closure::bind($callback, $this);

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

    /**
     * Get an array property by name
     */
    public function getPropertyArray(string $event): array
    {
        return $this->{$event};
    }

    /**
     * Get and/or create a new Repeater instance for this callback
     */
    public function repeater(): Repeater
    {
        return $this->repeater ??= Repeater::make();
    }

    /**
     * Specify how many times this callback's primary
     * callback should run
     */
    public function repeat(int $times): static
    {
        $this->repeater()->setMax($times);

        return $this;
    }

    /**
     * Specify a timeout for the story
     */
    public function timeout(int|float $amount, string $unit = Alarm::UNIT_MICROSECONDS): static
    {
        $this->alarm = Alarm::make($amount, $unit);

        return $this;
    }

    /**
     * Get the alarm
     */
    public function alarm(): ?Alarm
    {
        return $this->alarm;
    }

    /**
     * Get a random name for this instance
     */
    public static function getRandomName(): string
    {
        return static::class.'@'.Str::random(8);
    }
}
