<?php

namespace BradieTilley\Stories\PendingCalls;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Exceptions\StoryActionInvalidException;

class PendingActionCall
{
    /** @var array<array<string, mixed>> */
    protected array $pending = [];

    /**
     * @param  class-string  $class
     * @param  array<mixed>  $arguments
     */
    public function __construct(string $class, array $arguments)
    {
        $this->pending[] = [
            'name' => $class,
            'args' => $arguments,
        ];
    }

    /**
     * @param  string  $name
     * @param  array<mixed>  $arguments
     */
    public function __call($name, $arguments): static
    {
        $this->pending[] = [
            'name' => $name,
            'args' => $arguments,
        ];

        return $this;
    }

    /**
     * @param  string  $name
     */
    public function __get($name): static
    {
        $this->pending[] = [
            'name' => $name,
            'args' => null,
        ];

        return $this;
    }

    public function resolvePendingAction(): Action
    {
        if (empty($this->pending)) {
            // @codeCoverageIgnoreStart
            throw new \BadMethodCallException('Cannot run resolvePendingAction on PendingActionCall with no constructor');
            // @codeCoverageIgnoreEnd
        }

        // Fetch the namespace and construct arguments
        $construct = array_shift($this->pending);

        /** @var class-string $name */
        $name = $construct['name'];
        /** @var ?array<mixed> $args */
        $args = $construct['args'];

        /**
         * The action to create and run these pending calls against.
         * The action will always be what gets returned.
         */
        $action = new $name(...$args);

        if (! $action instanceof Action) {
            // @codeCoverageIgnoreStart
            throw StoryActionInvalidException::make($name);
            // @codeCoverageIgnoreEnd
        }

        /**
         * The pending result, which may differ from the Action if
         * a method on the action class returns a different class.
         */
        $result = $action;

        foreach ($this->pending as $invocation) {
            /** @var string $name */
            $name = $invocation['name'];

            /** @var ?array<mixed> $args */
            $args = $invocation['args'];

            if ($args === null) {
                $result = $result->{$name};
            } else {
                $result = $result->{$name}(...$args);
            }
        }

        return $action;
    }
}
