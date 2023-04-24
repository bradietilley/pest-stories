<?php

namespace BradieTilley\Stories\Helpers;

use Closure;
use Illuminate\Support\Collection;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

class CallbackReflection
{
    /** @var callable */
    protected $callback;

    /** @var ?array<string> */
    protected ?array $arguments = null;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public static function make(callable $callback): self
    {
        return new self($callback);
    }

    /**
     * @return array<string>
     */
    public function arguments(): array
    {
        if ($this->arguments !== null) {
            return $this->arguments;
        }

        $reflection = null;

        if ($this->callback instanceof Closure || is_string($this->callback)) {
            $reflection = new ReflectionFunction($this->callback);
        }

        if (is_array($this->callback) && isset($this->callback[0]) && isset($this->callback[1])) {
            $class = $this->callback[0];
            $method = $this->callback[1];

            $reflection = new ReflectionMethod($class, $method);
        }

        if ($reflection === null) {
            return $this->arguments = [];
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
