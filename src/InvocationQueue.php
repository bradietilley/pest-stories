<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Helpers\StoryAliases;
use Illuminate\Support\Collection;

class InvocationQueue
{
    /** @var array<Invocation> */
    public array $items = [];

    public function __construct()
    {
    }

    /**
     * Make a new queue
     */
    public static function make(): static
    {
        $class = StoryAliases::getClassAlias(InvocationQueue::class);

        /** @var static $class */
        return new $class(...func_get_args());
    }

    /**
     * Push an invocation to the queue
     */
    public function push(Invocation $invocation): static
    {
        $this->items[] = $invocation;

        return $this;
    }

    /**
     * Are there any items in the invocation queue?
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Compile the invocation queue to array for testing purposes
     */
    public function toArray(): array
    {
        return Collection::make($this->items)
            ->map(fn (Invocation $invocation) => $invocation->toArray())
            ->all();
    }

    /**
     * Inherit an invocation queue from a parent queue
     */
    public function inherit(InvocationQueue $parent): void
    {
        foreach ($parent->items as $invocation) {
            $this->push($invocation);
        }
    }
}
