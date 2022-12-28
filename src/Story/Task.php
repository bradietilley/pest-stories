<?php

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasOrder;
use Closure;
use Illuminate\Container\Container;
use BradieTilley\StoryBoard\Story;
use Illuminate\Support\Str;

class Task
{
    use HasName;
    use HasOrder;

    protected static array $registered = [];

    protected int $order;

    public function __construct(
        protected string $name,
        protected Closure $generator,
        ?int $order = null,
    ) {
        $this->order($order);
    }

    /**
     * Manually register the task (if not created via `make()`)
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
     * Fetch a task from the registrar
     */
    public static function fetch(string $name): Task
    {
        if (! isset(static::$registered[$name])) {
            throw StoryBoardException::taskNotFound($name);
        }

        return static::$registered[$name];
    }

    /**
     * Make and register this task
     * 
     * @return $this
     */
    public static function make(string $name, Closure $generator, ?int $order = null)
    {
        return (new self(
            name: $name,
            generator: $generator,
            order: $order,
        ))->register();
    }

    /**
     * Boot this task for the given story
     */
    public function boot(Story $story, array $arguments): mixed
    {
        $generator = $this->generator;
        $arguments = array_replace($story->getParameters(), $arguments);

        return Container::getInstance()->call($generator, $arguments);
    }

    /**
     * Get the name of the task to be referenced when building a story's set of tasks
     */
    public static function prepare(string|Closure|self $task): string
    {
        if (is_string($task)) {
            return $task;
        }

        if ($task instanceof Closure) {
            $task = self::make(
                name: 'inline_' . (string) Str::random(),
                generator: $task,
            );
        }

        // Don't re-register if already registered, to prevent overwriting existing task (in the event of duplicate names)
        if (! $task->registered()) {
            $task->register();
        }

        return $task->getName();
    }
}