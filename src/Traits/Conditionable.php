<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Traits;

use BradieTilley\Stories\Invocation;
use BradieTilley\Stories\InvocationQueue;
use Illuminate\Support\Traits\Conditionable as IlluminateConditionable;

trait Conditionable
{
    use IlluminateConditionable;

    public InvocationQueue $conditionables;

    /**
     * Run all previously recorded when and unless conditions
     */
    public function internalBootConditionables(): void
    {
        foreach ($this->conditionables->items as $invocation) {
            $invocation->setObject($this)->invoke();
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
    public function lazyWhen($value = null, callable $callback = null, callable $default = null)
    {
        $this->conditionables->push(
            Invocation::makeMethod(name: 'when', arguments: func_get_args()),
        );

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
    public function lazyUnless($value = null, callable $callback = null, callable $default = null)
    {
        $this->conditionables->push(
            Invocation::makeMethod(name: 'unless', arguments: func_get_args()),
        );

        return $this;
    }
}
