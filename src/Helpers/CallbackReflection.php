<?php

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Exceptions\FailedToIdentifyCallbackArgumentsException;
use Closure;
use Illuminate\Support\Collection;
use Pest\Exceptions\ShouldNotHappen;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

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

        try {
            $reflection = null;
            $callback = $this->callback;

            if ($callback instanceof Closure || is_string($callback)) {
                $reflection = new ReflectionFunction($callback);
            }

            if (is_array($callback) && isset($callback[0]) && isset($callback[1])) {
                $class = $callback[0];
                $method = $callback[1];

                $reflection = new ReflectionMethod($class, $method);
            }

            if ($reflection === null) {
                // @codeCoverageIgnoreStart
                throw ShouldNotHappen::fromMessage('Callback reflection format not supported');
                // @codeCoverageIgnoreEnd
            }

            /** @var array<string> $arguments */
            $arguments = Collection::make($reflection->getParameters())
                ->map(function (ReflectionParameter $parameter) {
                    return $parameter->getName();
                })
                ->toArray();
        } catch (Throwable $exception) {
            // @codeCoverageIgnoreStart
            throw FailedToIdentifyCallbackArgumentsException::make($exception);
            // @codeCoverageIgnoreEnd
        }

        return $this->arguments = $arguments;
    }
}
