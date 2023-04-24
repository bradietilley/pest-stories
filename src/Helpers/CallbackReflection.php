<?php

namespace BradieTilley\Stories\Helpers;

use Closure;
use Illuminate\Support\Collection;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

class CallbackReflection
{
    /** @var array<string>|callable */
    protected $callback;

    /** @var ?array<string> */
    protected ?array $arguments = null;

    /**
     * @param  array<string>|callable  $callback
     */
    public function __construct(array|callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param  array<string>|callable  $callback
     */
    public static function make(array|callable $callback): self
    {
        return new self($callback);
    }

    /**
     * @return array<string>
     */
    public function arguments(): array
    {
        if ($this->arguments !== null) {
            // @codeCoverageIgnoreStart
            return $this->arguments;
            // @codeCoverageIgnoreEnd
        }

        $reflection = null;
        $callback = $this->callback;

        if ($callback instanceof Closure || is_string($callback)) {
            $reflection = new ReflectionFunction($callback);
        } else {
            $class = $callback[0] ?? '';
            $method = $callback[1] ?? '';

            $reflection = new ReflectionMethod($class, $method);
        }

        /** @var array<string> $arguments */
        $arguments = Collection::make($reflection->getParameters())
            ->map(function (ReflectionParameter $parameter) {
                return $parameter->getName();
            })
            ->toArray();

        return $this->arguments = $arguments;
    }
}
