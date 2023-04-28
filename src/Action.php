<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Concerns\Binds;
use BradieTilley\Stories\Concerns\Events;
use BradieTilley\Stories\Concerns\Repeats;
use BradieTilley\Stories\Concerns\Reposes;
use BradieTilley\Stories\Concerns\Times;
use BradieTilley\Stories\Contracts\Deferred;
use BradieTilley\Stories\Exceptions\ActionMustAcceptAllDatasetArgumentsException;
use BradieTilley\Stories\Exceptions\StoryActionInvalidException;
use BradieTilley\Stories\Exceptions\StoryActionNotFoundException;
use BradieTilley\Stories\Helpers\CallbackReflection;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\PendingCalls\PendingActionCall;
use BradieTilley\Stories\Repositories\Actions;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionProperty;

/**
 * @method static static|PendingActionCall make(string $name = null, ?Closure $callback = null, string $variable = null)
 */
class Action
{
    use Repeats;
    use Times;
    use Events;
    use Binds;
    use Reposes;

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

    /** Does this action expect its arguments to be the dataset, verbatim to what is provided by the test? */
    protected bool $requiresDataset = false;

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
    public function callCallback(Story $story, callable $callback, array $additional = []): mixed
    {
        if ($this->requiresDataset) {
            /** @var string|array<string>|Closure $callback */
            $argumentNames = CallbackReflection::make($callback)->arguments();
            $newArguments = [];

            $index = 0;
            foreach ($story->dataset()->all() as $argument) {
                $index++;
                $argumentName = array_shift($argumentNames);

                // Shouldn't be asking for the dataset if you're not going to utilise the dataset
                if ($argumentName === null) {
                    throw ActionMustAcceptAllDatasetArgumentsException::make(
                        action: $this,
                        datasetIndexMissing: $index,
                    );
                }

                $newArguments[$argumentName] = $argument;
            }

            $additional = array_replace($additional, $newArguments);
        }

        /** @var callable $callback */
        return $story->callCallback($callback, $additional);
    }

    /**
     * Run the action against the given story
     *
     * @param  array<string, mixed>  $arguments
     */
    private function process(Story $story, array $arguments = [], string $variable = null): void
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
        $value = $this->callCallback($story, $callback, [
            'action' => $this,
        ] + $arguments);

        /**
         * Record the value returned from either the __invoke
         * method or the Closure callback against the story using
         * the variable specified with this action.
         */
        $variable ??= $this->getVariable();
        $story->set($variable, $value);

        $this->callbackRun('after');
    }

    /**
     * Run the action against the given story, with
     * repeating, etc.
     *
     * @param  array<string, mixed>  $arguments
     */
    public function run(Story $story = null, array $arguments = [], string $variable = null): void
    {
        $story ??= story();

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
            $action = new Action('inline@'.Str::random(8), $action);
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
     * Expect the dataset to be passed to this action
     *
     * @codeCoverageIgnore -- this method is run but code coverage is turning a blind eye
     */
    public function dataset(): static
    {
        $this->requiresDataset = true;

        return $this;
    }

    /**
     * Does this action require the dataset
     */
    public function requiresDataset(): bool
    {
        return $this->requiresDataset;
    }
}
