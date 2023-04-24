<?php

namespace BradieTilley\Stories\PendingCalls;

/**
 * @template TObject of object
 *
 * @mixin TObject
 */
class PendingCall
{
    /** @var array<array<string, mixed>> */
    protected array $pending = [];

    /** @var TObject */
    protected $object;

    /**
     * @param  TObject  $object
     */
    public function __construct(object $object)
    {
        $this->object = $object;
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

    /**
     * @return TObject
     */
    public function invokePendingCall(): object
    {
        $object = $this->object;

        foreach ($this->pending as $invocation) {
            /** @var string $name */
            $name = $invocation['name'];

            /** @var ?array<mixed> $args */
            $args = $invocation['args'];

            if ($args === null) {
                $object = $object->{$name};
            } else {
                $object = $object->{$name}(...$args);
            }
        }

        return $this->object;
    }
}
