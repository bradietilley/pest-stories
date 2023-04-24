<?php

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Exceptions\FailedToIdentifyCallbackArgumentsException;
use Closure;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

class CallbackReflection
{
    /** @var array<string>|string|Closure */
    protected $callback;

    /** @var ?array<string> */
    protected ?array $arguments = null;

    /**
     * @param  array<string>|string|Closure  $callback
     */
    public function __construct(array|string|Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param  array<string>|string|Closure  $callback
     */
    public static function make(array|string|Closure $callback): self
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
                throw new InvalidArgumentException('Callback reflection format not supported');
            }

            /** @var array<string> $arguments */
            $arguments = Collection::make($reflection->getParameters())
                ->map(function (ReflectionParameter $parameter) {
                    return $parameter->getName();
                })
                ->toArray();
        } catch (Throwable $exception) {
            throw FailedToIdentifyCallbackArgumentsException::make($exception);
        }

        return $this->arguments = $arguments;
    }
}
