<?php

namespace BradieTilley\Stories\Helpers;

use BradieTilley\Stories\Exceptions\FailedToIdentifyCallbackArgumentsException;
use Closure;
use Illuminate\Support\Str;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;

/** @property array<string|object>|string|Closure $callback */
class ReflectionCallback
{
    /** @var ?array<string> */
    protected ?array $arguments = null;

    /**
     * A unique name for this callback (see uniqueName() method)
     */
    protected ?string $uniqueName = null;

    /**
     * The underlying ReflectionFunctionAbstract instance
     */
    protected ReflectionFunction|ReflectionMethod|null $reflection = null;

    /**
     * The callback function, method or Closure
     *
     * @var array<string|object>|string|Closure
     */
    protected array|string|Closure $callback;

    /**
     * @param  array<string|object>|string|Closure|callable  $callback
     */
    public function __construct(array|string|Closure|callable $callback)
    {
        /** @phpstan-ignore-next-line */
        $this->callback = $callback;
    }

    /**
     * @param  array<string|object>|string|Closure|callable  $callback
     */
    public static function make(array|string|Closure|callable $callback): self
    {
        return new self($callback);
    }

    /**
     * Get the reflection of the method or function
     */
    public function reflection(): ReflectionFunction|ReflectionMethod
    {
        if ($this->reflection !== null) {
            // @codeCoverageIgnoreStart
            return $this->reflection;
            // @codeCoverageIgnoreEnd
        }

        $callback = $this->callback;

        if ($callback instanceof Closure || is_string($callback)) {
            return $this->reflection = new ReflectionFunction($callback);
        }

        if (is_array($callback) && isset($callback[0]) && isset($callback[1])) {
            /** @var string|object $class */
            $class = $callback[0];
            /** @var string $method */
            $method = $callback[1];

            return $this->reflection = new ReflectionMethod($class, $method);
        }

        throw new InvalidArgumentException('Callback reflection format not supported');
    }

    /**
     * Get a list of argument names expected by the given closure
     *
     * @return array<string>
     */
    public function arguments(): array
    {
        if ($this->arguments !== null) {
            // @codeCoverageIgnoreStart
            return $this->arguments;
            // @codeCoverageIgnoreEnd
        }

        $this->arguments = [];

        try {
            foreach ($this->reflection()->getParameters() as $parameter) {
                $this->arguments[] = $parameter->getName();
            }
        } catch (Throwable $exception) {
            throw FailedToIdentifyCallbackArgumentsException::make($exception);
        }

        return $this->arguments;
    }

    /**
     * Get a unique name for this callback.
     *
     * The format of the name for closures will be:
     *
     *     inline@\Path\to\FileWithClosure.php:123[RaNd0M]
     *
     * or for non-closures it will be:
     *
     *     func@\Path\to\ClassWithMethod.php:456[R4nD0m]
     */
    public function uniqueName(): string
    {
        if ($this->uniqueName !== null) {
            // @codeCoverageIgnoreStart
            return $this->uniqueName;
            // @codeCoverageIgnoreEnd
        }

        $type = ($this->callback instanceof Closure) ? 'inline' : 'func';
        $file = $this->reflection()->getFileName();
        $line = $this->reflection()->getStartLine();
        $rand = Str::random(8);

        $name = sprintf(
            '%s@%s:%d[%s]',
            $type,
            $file,
            $line,
            $rand,
        );

        return $this->uniqueName = $name;
    }

    public function exceptionName(): string
    {
        $callback = $this->callback;

        if (is_string($callback)) {
            return sprintf('function: `%s()`', $callback);
        }

        if (is_array($callback)) {
            if (count($callback) === 2) {
                $object = $callback[0] ?? null;
                $method = $callback[1] ?? null;

                if ((is_string($object) || is_object($object)) && is_string($method)) {
                    if (is_object($object)) {
                        $object = get_class($object);
                    }

                    return sprintf('method: `%s::%s()`', $object, $method);
                }
            }

            return 'method: <unknown array format>';
        }

        $reflection = ReflectionCallback::make($callback);

        return sprintf(
            'callable: `%s:%d`',
            $reflection->reflection()->getFileName(),
            $reflection->reflection()->getStartLine(),
        );
    }
}
