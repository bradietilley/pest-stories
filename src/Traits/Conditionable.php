<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Traits;

use Illuminate\Support\Traits\Conditionable as IlluminateConditionable;

trait Conditionable
{
    use IlluminateConditionable {
        when as illuminateWhen;
        unless as illuminateUnless;
    }

    /** @var array<int, array<string, string|array<mixed>>> */
    protected array $conditionables = [];

    /**
     * Run all previously recorded when and unless conditions
     */
    public function internalBootConditionables(): void
    {
        foreach ($this->conditionables as $conditionable) {
            /** @var string $type */
            $type = $conditionable['type'];
            /** @var array<mixed> $args */
            $args = $conditionable['args'];

            $method = 'illuminate'.ucfirst($type);

            $this->{$method}(...$args);
        }
    }

    /**
     * Apply the callback if the given "value" is (or resolves to) truthy.
     *
     * @template TWhenParameter
     * @template TWhenReturnType
     *
     * @param  (\Closure($this): TWhenParameter)|TWhenParameter|null  $value
     * @param  (callable($this, TWhenParameter): TWhenReturnType)|null  $callback
     * @param  (callable($this, TWhenParameter): TWhenReturnType)|null  $default
     * @return $this|TWhenReturnType
     */
    public function when($value = null, callable $callback = null, callable $default = null)
    {
        $this->conditionables[] = [
            'type' => 'when',
            'args' => func_get_args(),
        ];

        return $this;
    }

    /**
     * Apply the callback if the given "value" is (or resolves to) falsy.
     *
     * @template TUnlessParameter
     * @template TUnlessReturnType
     *
     * @param  (\Closure($this): TUnlessParameter)|TUnlessParameter|null  $value
     * @param  (callable($this, TUnlessParameter): TUnlessReturnType)|null  $callback
     * @param  (callable($this, TUnlessParameter): TUnlessReturnType)|null  $default
     * @return $this|TUnlessReturnType
     */
    public function unless($value = null, callable $callback = null, callable $default = null)
    {
        $this->conditionables[] = [
            'type' => 'unless',
            'args' => func_get_args(),
        ];

        return $this;
    }
}
