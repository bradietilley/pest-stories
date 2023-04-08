<?php

namespace BradieTilley\Stories;

use BradieTilley\Stories\Helpers\StoryAliases;
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
}
