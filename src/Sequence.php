<?php

namespace BradieTilley\Stories;

use BradieTilley\Stories\Helpers\StoryAliases;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use InvalidArgumentException;

/**
 * @mixin Collection
 */
class Sequence
{
    use ForwardsCalls;

    /**
     * @var Collection<int, Callback>
     */
    protected Collection $items;

    public function __construct()
    {
        $this->items = Collection::make();
    }

    /**
     * @param  string  $method
     * @param  array  $parameters
     */
    public function __call($method, $parameters): mixed
    {
        return $this->forwardDecoratedCallTo($this->items, $method, $parameters);
    }

    /**
     * @param  iterable<Callback>  $callbacks
     */
    public static function make(iterable $callbacks = []): static
    {
        $class = StoryAliases::getClassAlias(Sequence::class);

        /** @var static $sequence */
        $sequence = new $class();

        return $sequence->pushCallbacks($callbacks);
    }

    /**
     * @param  iterable<Callback>  $callbacks
     */
    public function pushCallbacks(iterable $callbacks = []): static
    {
        foreach ($callbacks as $callback) {
            if (! $callback instanceof Callback) {
                throw new InvalidArgumentException('Unsupported callback in Sequence');
            }

            $this->push($callback);
        }

        return $this;
    }

    public function boot(array $arguments = []): void
    {
        foreach ($this->items->all() as $callback) {
            $callback->boot($arguments);
        }
    }

    /**
     * Add an action for this story
     *
     * @param  array<string|Action|Closure>|string|Action|Closure  $action
     */
    public function action(array|string|Action|Closure $action, array $arguments = []): static
    {
        $action = (is_array($action)) ? $action : [$action];

        $action = array_map(
            fn (string|Action|Closure $action): Action => Action::prepare($action),
            $action,
        );

        foreach ($action as $actionItem) {
            $this->push($actionItem->with($arguments));
        }

        return $this;
    }

    /**
     * Add an assertion for this story
     *
     * @param  array<string|Assertion|Closure>|string|Assertion|Closure  $assertion
     */
    public function assertion(array|string|Assertion|Closure $assertion, array $arguments = []): static
    {
        $assertion = (is_array($assertion)) ? $assertion : [$assertion];

        $assertion = array_map(
            fn (string|Assertion|Closure $assertion): Assertion => Assertion::prepare($assertion),
            $assertion,
        );

        foreach ($assertion as $assertionItem) {
            $this->push($assertionItem->with($arguments));
        }

        return $this;
    }
}
