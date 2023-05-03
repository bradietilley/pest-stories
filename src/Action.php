<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Concerns\Binds;
use BradieTilley\Stories\Concerns\Events;
use BradieTilley\Stories\Concerns\ProxiesData;
use BradieTilley\Stories\Concerns\Repeats;
use BradieTilley\Stories\Concerns\Reposes;
use BradieTilley\Stories\Concerns\Times;
use BradieTilley\Stories\Contracts\Deferred;
use BradieTilley\Stories\Exceptions\StoryActionInvalidException;
use BradieTilley\Stories\Exceptions\StoryActionNotFoundException;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\PendingCalls\PendingActionCall;
use BradieTilley\Stories\Repositories\Actions;
use BradieTilley\Stories\Repositories\DataRepository;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use ReflectionClass;
use ReflectionFunction;
use ReflectionProperty;

/**
 * @method static static|PendingActionCall make(string $name = null, ?Closure $callback = null, string $variable = null)
 */
class Action
{
    use Binds;
    use Conditionable;
    use Events;
    use ProxiesData;
    use Repeats;
    use Reposes;
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
     * @var Collection<int, Action|PendingActionCall>
     */
    protected Collection $actions;

    /**
     * Internal data reposity for this action alone.
     *
     * Unlike that provided by `Reposes` trait, data
     * persisted here is not shared back to the story.
     */
    public DataRepository $internal;

    public function __construct(string $name = null, ?Closure $callback = null, string $variable = null)
    {
        if (! $this->initializedProperty('name')) {
            $this->name = $name ?? self::getRandomName();
        }

        if (! $this->initializedProperty('variable')) {
            $this->variable = $variable ?? $this->name;
        }

        $this->callback = $callback;
        $this->internal = new DataRepository();
        $this->actions = Collection::make();
        $this->store();
        $this->boot();
    }

    /**
     * Boot this action and all of its traits
     */
    public function boot(): void
    {
        foreach (class_uses_recursive($this) as $trait) {
            $method = 'boot'.Str::afterLast($trait, '\\');

            if (method_exists($this, $method)) {
                $this->{$method}();
            }
        }
    }

    /**
     * Determine if the property has been initialized yet
     */
    protected function initializedProperty(string $property): bool
    {
        return (new ReflectionProperty($this, $property))->isInitialized($this);
    }

    /**
     * Statically create this action.
     * Note: If the action is a Deferred action (see interface) then
     * the action returned is a pending call.
     */
    public static function make(): PendingActionCall|static
    {
        // If deferred by default, we must return a PendingActionCall instead (defer construction and calculation of the object)
        if (self::isDeferredAction(static::class)) {
            return static::defer(...func_get_args());
        }

        /** @phpstan-ignore-next-line */
        return new static(...func_get_args());
    }

    /**
     * Statically create and defer the building of this action
     * Note: Only a `PendingCall` instance is returned, never `static`. `static` is only provided to assist IDEs.
     *
     * @phpstan-ignore-next-line
     *
     * @return PendingActionCall|static
     */
    public static function defer(): PendingActionCall
    {
        return new PendingActionCall(static::class, func_get_args());
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
    public function store(): static
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
    public function action(string|Closure|Action $action): static|PendingActionCall
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
     * Call the given callback with dependency injection
     *
     * @param  array<string, mixed>  $additional
     */
    public function call(Story $story, callable $callback, array $additional = []): mixed
    {
        /** @var callable $callback */
        return $story->call($callback, $additional);
    }

    /**
     * Run the action against the given story
     *
     * @param  array<string, mixed>  $arguments
     */
    private function process(Story $story, array $arguments = [], string $variable = null): mixed
    {
        $this->callbackRun('before');

        /**
         * If this action calls for other actions to be run, then run those
         * other actions first.
         */
        $this->actions
            ->map(fn (Action|PendingActionCall $action) => self::resolve($action))
            ->each(fn (Action $action) => $action->fresh($story)->run($story));

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
            $callback = $this->bindToPreferred($this->callback);
        }

        /**
         * Call the callback (__invoke method or Closure callback)
         * with the story's arguments
         */
        $value = $this->call($story, $callback, [
            'action' => $this,
        ] + $arguments);

        /**
         * If the value returned is a pending action call or an action
         * that is not the current action then we'll run that action
         * right here right now.
         *
         * Typically this is seen when an inline action is defined and
         * returns another action, which is an alternative way of
         * deferring the computation of the action's chained methods.
         *
         * Example:
         *
         *     test('do something')
         *         ->action(fn () => CreateUser::make()->admin()->login(), variable: 'user');
         *
         * The value of the action (inline action callback) becomes the
         * value returned from the action within, so in the example above
         * the 'user' variable becomes a User model (presuming CreateUser
         * returns a User model).
         */
        $value = $this->resolveValue($value, $story, $arguments, $variable);

        /**
         * Record the value returned from either the __invoke
         * method or the Closure callback against the story using
         * the variable specified with this action.
         */
        $variable ??= $this->getVariable();
        $story->set($variable, $value);

        $this->callbackRun('after');

        return $value;
    }

    /**
     * Run the action against the given story, with
     * repeating, etc.
     *
     * @param  array<string, mixed>  $arguments
     */
    public function run(Story $story = null, array $arguments = [], string $variable = null): mixed
    {
        $story ??= story();

        if ($this->hasTimer()) {
            $this->timer()->start();
        }

        $result = null;

        while ($this->repeats()) {
            $this->repeatsIncrement();

            $result = $this->process($story, arguments: $arguments, variable: $variable);
        }

        if ($this->hasTimer()) {
            $this->timer()->end()->check();
        }

        return $result;
    }

    /**
     * Determine if the given class A) exists and B) is either the base
     * Action class or a subclass of the Action class
     */
    public static function isAction(string $class): bool
    {
        if ($class === Action::class) {
            return true;
        }

        if (! class_exists($class)) {
            return false;
        }

        return (new ReflectionClass($class))->isSubclassOf(Action::class);
    }

    /**
     * Determine if the given class A) is an action class and B) is a Deferred action.
     *
     * @param  class-string  $class
     */
    public static function isDeferredAction(string $class): bool
    {
        if (! self::isAction($class)) {
            return false;
        }

        $deferred = (new ReflectionClass($class))->implementsInterface(Deferred::class);

        return $deferred;
    }

    /**
     * Fetch the given action (disregard the current action object)
     *
     * @throws StoryActionNotFoundException
     */
    public function fetch(string $name): Action
    {
        return Actions::fetch($name);
    }

    /**
     * Convert the given action into an ActionCall.
     *
     * string - lookup Action of the same name
     * Closure - create Action for this closure
     * Action - returns itself
     * PendingActionCall - returns itself
     */
    public static function parse(string|Closure|Action|PendingActionCall $action): Action|PendingActionCall
    {
        if (is_string($action) && class_exists($action)) {
            if (! self::isAction($action)) {
                throw StoryActionInvalidException::make($action);
            }

            $action = $action::make();
            /** @var Action|PendingActionCall $action */
        }

        if (is_string($action)) {
            $action = Actions::fetch($action);
        }

        if ($action instanceof Closure) {
            $action = new Action(self::generateNameForClosure($action), $action);
        }

        return $action;
    }

    /**
     * Resolve the pending action call or return the Action
     */
    public static function resolve(Action|PendingActionCall $action): Action
    {
        return ($action instanceof PendingActionCall) ? $action->resolvePendingAction() : $action;
    }

    /**
     * Resolve the given Pending Action Call, Action or return the given value
     *
     * @param  array<mixed>  $arguments
     */
    public function resolveValue(mixed $action, Story $story, array $arguments = [], string $variable = null): mixed
    {
        if ($action instanceof PendingActionCall) {
            $action = $this->resolve($action);
        }

        if (($action instanceof Action) && ($action !== $this)) {
            $action = $action->run(
                story: $story,
                arguments: $arguments,
                variable: $variable
            );
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

    /**
     * Generate a name from the given closure
     */
    public static function generateNameForClosure(Closure $closure): string
    {
        $reflection = new ReflectionFunction($closure);

        $file = $reflection->getFileName();
        $line = $reflection->getStartLine();
        $rand = Str::random(8);

        $name = sprintf(
            'inline@%s:%d[%s]',
            $file,
            $line,
            $rand,
        );

        return $name;
    }
}
