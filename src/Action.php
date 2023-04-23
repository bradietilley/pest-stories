<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Concerns\Repeats;
use BradieTilley\Stories\Concerns\Times;
use BradieTilley\Stories\Exceptions\StoryActionInvalidException;
use BradieTilley\Stories\Repositories\Actions;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionProperty;

/**
 * @method static static make(string $name = null, ?Closure $callback = null, string $variable = null)
 */
class Action
{
    use Repeats;
    use Times;

    /**
     * The name of the action
     */
    protected string $name;

    /**
     * The name of the variable
     */
    protected string $variable;

    /**
     * The callback to run, if not invokable via __invoke
     */
    protected ?Closure $callback = null;

    /**
     * @var Collection<int, Action>
     */
    protected Collection $actions;

    public function __construct(string $name = null, ?Closure $callback = null, string $variable = null)
    {
        if (! $this->initializedProperty('name')) {
            $this->name = $name ?? self::getRandomName();
        }

        if (! $this->initializedProperty('variable')) {
            $this->variable = $variable ?? $this->name;
        }

        $this->callback = $callback;
        $this->actions = Collection::make();
        $this->remember();
    }

    /**
     * Determine if the property has been initialized yet
     */
    protected function initializedProperty(string $property): bool
    {
        return (new ReflectionProperty($this, $property))->isInitialized($this);
    }

    /**
     * Statically create this action
     */
    public static function make(): static
    {
        /** @phpstan-ignore-next-line */
        return new static(...func_get_args());
    }

    /**
     * Get the name of this action object
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get a random name to use for this action
     */
    public static function getRandomName(): string
    {
        return static::class.'@'.Str::random(8);
    }

    /**
     * Store this action in the action repository so that
     * it can be referenced later
     */
    public function remember(): static
    {
        Actions::store($this->name, $this);

        return $this;
    }

    /**
     * Set the callback to invoke when running this action
     */
    public function as(?Closure $callback = null): static
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Set the name of this action
     */
    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Require this action to be run as part of this action.
     */
    public function action(string|Closure|Action $action): static
    {
        $action = Action::parse($action);
        $this->actions->push($action);

        return $this;
    }

    /**
     * Clone this action call for the given story.
     */
    public function fresh(Story $story): static
    {
        return clone $this;
    }

    /**
     * Run the action against the given story
     */
    private function process(Story $story, array $arguments = [], string $variable = null): void
    {
        /**
         * If this action calls for other actions to be run, then run those
         * other actions first.
         */
        $this->actions->each(fn (Action $action) => $action->fresh($story)->run($story));

        /**
         * Default to use the __invoke method as the callback
         *
         * @var callable $callback
         */
        $callback = [$this, '__invoke'];

        /**
         * If this action has a Closure callback then we'll use that
         * instead of the __invoke method
         */
        if ($this->callback !== null) {
            $callback = \Pest\Support\Closure::bind($this->callback, $story);
        }

        /**
         * Call the callback (__invoke method or Closure callback)
         * with the story's arguments
         */
        $value = $story->callCallback($callback, [
            'action' => $this,
        ] + $arguments);

        /**
         * Record the value returned from either the __invoke
         * method or the Closure callback against the story using
         * the variable specified with this action.
         */
        $variable ??= $this->getVariable();
        $story->setData($variable, $value);
    }

    /**
     * Run the action against the given story, with
     * repeating, etc.
     */
    public function run(Story $story, array $arguments = [], string $variable = null): void
    {
        if ($this->hasTimer()) {
            $this->timer()->start();
        }

        while ($this->repeats()) {
            $this->repeatsIncrement();

            $this->process($story, arguments: $arguments, variable: $variable);
        }

        if ($this->hasTimer()) {
            $this->timer()->end()->check();
        }
    }

    /**
     * Convert the given action into an ActionCall.
     *
     * string - lookup Action of the same name
     * Closure - create Action for this closure
     * ActionCall - returns itself
     */
    public static function parse(string|Closure|Action $action): Action
    {
        if (is_string($action) && class_exists($action)) {
            $action = Container::getInstance()->make($original = $action);

            if (! $action instanceof Action) {
                throw StoryActionInvalidException::make($original);
            }
        }

        if (is_string($action)) {
            $action = Actions::fetch($action);
        }

        if ($action instanceof Closure) {
            $action = new Action('inline@'.Str::random(8), $action);
        }

        return $action;
    }

    /**
     * Get the name of the variable to use when storing the
     * action's result against the story
     */
    public function getVariable(): string
    {
        return $this->variable;
    }

    /**
     * Set the name of the variable to use when storing the
     * action's result against the story
     */
    public function variable(string $variable): static
    {
        $this->variable = $variable;

        return $this;
    }
}
