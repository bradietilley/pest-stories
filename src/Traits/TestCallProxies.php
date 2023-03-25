<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Traits;

use Closure;
use Pest\PendingCalls\TestCall;
use Tests\Mocks\PestStoriesMockTestCall;

/**
 * @method self throws(string|int $exception, string $exceptionMessage = null, int $exceptionCode = null)
 * @method self throwsIf(callable|bool $condition, string|int $exception, string $exceptionMessage = null, int $exceptionCode = null)
 * @method self depends(string ...$depends)
 * @method self group(string ...$groups)
 * @method self skip(Closure|bool|string $conditionOrMessage = true, string $message = '')
 * @method self todo()
 * @method self covers(string ...$classesOrFunctions)
 * @method self coversClass(string ...$classes)
 * @method self coversFunction(string ...$functions)
 * @method self coversNothing()
 * @method self throwsNoExceptions()
 */
trait TestCallProxies
{
    protected array $testCallProxies = [];

    /**
     * Asserts that the test throws the given `$exceptionClass` when called.
     */
    public function throws(string|int $exception, string $exceptionMessage = null, int $exceptionCode = null): static
    {
        return $this->recordTestCallProxy(__FUNCTION__, func_get_args());
    }

    /**
     * Asserts that the test throws the given `$exceptionClass` when called if the given condition is true.
     *
     * @param (callable(): bool)|bool $condition
     */
    public function throwsIf(callable|bool $condition, string|int $exception, string $exceptionMessage = null, int $exceptionCode = null): static
    {
        return $this->recordTestCallProxy(__FUNCTION__, func_get_args());
    }

    /**
     * Sets the test depends.
     */
    public function depends(string ...$depends): static
    {
        return $this->recordTestCallProxy(__FUNCTION__, func_get_args());
    }

    /**
     * Sets the test group(s).
     */
    public function group(string ...$groups): static
    {
        return $this->recordTestCallProxy(__FUNCTION__, func_get_args());
    }

    /**
     * Skips the current test.
     */
    public function skip(Closure|bool|string $conditionOrMessage = true, string $message = ''): static
    {
        return $this->recordTestCallProxy(__FUNCTION__, func_get_args());
    }

    /**
     * Sets the test as "todo".
     */
    public function todo(): static
    {
        return $this->recordTestCallProxy(__FUNCTION__, func_get_args());
    }

    /**
     * Sets the covered classes or methods.
     */
    public function covers(string ...$classesOrFunctions): static
    {
        return $this->recordTestCallProxy(__FUNCTION__, func_get_args());
    }

    /**
     * Sets the covered classes.
     */
    public function coversClass(string ...$classes): static
    {
        return $this->recordTestCallProxy(__FUNCTION__, func_get_args());
    }

    /**
     * Sets the covered functions.
     */
    public function coversFunction(string ...$functions): static
    {
        return $this->recordTestCallProxy(__FUNCTION__, func_get_args());
    }

    /**
     * Sets that the current test covers nothing.
     */
    public function coversNothing(): static
    {
        return $this->recordTestCallProxy(__FUNCTION__, func_get_args());
    }

    /**
     * Informs the test runner that no expectations happen in this test,
     * and its purpose is simply to check whether the given code can
     * be executed without throwing exceptions.
     */
    public function throwsNoExceptions(): static
    {
        return $this->recordTestCallProxy(__FUNCTION__, func_get_args());
    }

    /**
     * Record a proxied call to the TestCall object
     */
    public function recordTestCallProxy(string $method, array $arguments): static
    {
        $this->testCallProxies[$method] ??= [];
        $this->testCallProxies[$method][] = $arguments;

        return $this;
    }

    /**
     * Apply all proxies to the TestCall object
     */
    public function applyTestCallProxies(TestCall|PestStoriesMockTestCall $testCall): void
    {
        foreach ($this->testCallProxies as $method => $invokation) {
            foreach ($invokation as $arguments) {
                $testCall->{$method}(...$arguments);
            }
        }
    }
}
