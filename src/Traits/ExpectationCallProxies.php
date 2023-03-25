<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Traits;

use DateTimeInterface;
use Pest\Matchers\Any;
use Pest\Mixins\Expectation;
use PHPUnit\Framework\Constraint\Constraint;

/**
 * These methods are synonymous with Pest\Mixins\Expectation
 * and are used in favour of an @mixin because: A) less
 * magic is better, B) named argument support.
 *
 * @mixin Expectation
 */
trait ExpectationCallProxies
{
    /**
     * Register a `toBe()` assertion for the previous expectation value
     */
    public function toBe(mixed $expected, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeEmpty()` assertion for the previous expectation value
     */
    public function toBeEmpty(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeTrue()` assertion for the previous expectation value
     */
    public function toBeTrue(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeTruthy()` assertion for the previous expectation value
     */
    public function toBeTruthy(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeFalse()` assertion for the previous expectation value
     */
    public function toBeFalse(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeFalsy()` assertion for the previous expectation value
     */
    public function toBeFalsy(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeGreaterThan()` assertion for the previous expectation value
     */
    public function toBeGreaterThan(int|float|DateTimeInterface $expected, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeGreaterThanOrEqual()` assertion for the previous expectation value
     */
    public function toBeGreaterThanOrEqual(int|float|DateTimeInterface $expected, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeLessThan()` assertion for the previous expectation value
     */
    public function toBeLessThan(int|float|DateTimeInterface $expected, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeLessThanOrEqual()` assertion for the previous expectation value
     */
    public function toBeLessThanOrEqual(int|float|DateTimeInterface $expected, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toContain()` assertion for the previous expectation value
     */
    public function toContain(mixed ...$needles): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toStartWith()` assertion for the previous expectation value
     */
    public function toStartWith(string $expected, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toEndWith()` assertion for the previous expectation value
     */
    public function toEndWith(string $expected, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toHaveLength()` assertion for the previous expectation value
     */
    public function toHaveLength(int $number, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toHaveCount()` assertion for the previous expectation value
     */
    public function toHaveCount(int $count, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toHaveProperty()` assertion for the previous expectation value
     */
    public function toHaveProperty(string $name, mixed $value = new Any(), string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toHaveProperties()` assertion for the previous expectation value
     */
    public function toHaveProperties(iterable $names, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toHaveMethod()` assertion for the previous expectation value
     */
    public function toHaveMethod(string $name, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toHaveMethods()` assertion for the previous expectation value
     */
    public function toHaveMethods(iterable $names, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toEqual()` assertion for the previous expectation value
     */
    public function toEqual(mixed $expected, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toEqualCanonicalizing()` assertion for the previous expectation value
     */
    public function toEqualCanonicalizing(mixed $expected, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toEqualWithDelta()` assertion for the previous expectation value
     */
    public function toEqualWithDelta(mixed $expected, float $delta, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeIn()` assertion for the previous expectation value
     */
    public function toBeIn(iterable $values, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeInfinite()` assertion for the previous expectation value
     */
    public function toBeInfinite(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeInstanceOf()` assertion for the previous expectation value
     */
    public function toBeInstanceOf(string $class, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeArray()` assertion for the previous expectation value
     */
    public function toBeArray(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeBool()` assertion for the previous expectation value
     */
    public function toBeBool(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeCallable()` assertion for the previous expectation value
     */
    public function toBeCallable(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeFloat()` assertion for the previous expectation value
     */
    public function toBeFloat(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeInt()` assertion for the previous expectation value
     */
    public function toBeInt(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeIterable()` assertion for the previous expectation value
     */
    public function toBeIterable(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeNumeric()` assertion for the previous expectation value
     */
    public function toBeNumeric(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeObject()` assertion for the previous expectation value
     */
    public function toBeObject(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeResource()` assertion for the previous expectation value
     */
    public function toBeResource(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeScalar()` assertion for the previous expectation value
     */
    public function toBeScalar(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeString()` assertion for the previous expectation value
     */
    public function toBeString(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeJson()` assertion for the previous expectation value
     */
    public function toBeJson(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeNan()` assertion for the previous expectation value
     */
    public function toBeNan(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeNull()` assertion for the previous expectation value
     */
    public function toBeNull(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toHaveKey()` assertion for the previous expectation value
     */
    public function toHaveKey(string|int $key, mixed $value = new Any(), string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toHaveKeys()` assertion for the previous expectation value
     */
    public function toHaveKeys(array $keys, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeDirectory()` assertion for the previous expectation value
     */
    public function toBeDirectory(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeReadableDirectory()` assertion for the previous expectation value
     */
    public function toBeReadableDirectory(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeWritableDirectory()` assertion for the previous expectation value
     */
    public function toBeWritableDirectory(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeFile()` assertion for the previous expectation value
     */
    public function toBeFile(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeReadableFile()` assertion for the previous expectation value
     */
    public function toBeReadableFile(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toBeWritableFile()` assertion for the previous expectation value
     */
    public function toBeWritableFile(string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toMatchArray()` assertion for the previous expectation value
     */
    public function toMatchArray(iterable $array, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toMatchObject()` assertion for the previous expectation value
     */
    public function toMatchObject(iterable $object, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toMatch()` assertion for the previous expectation value
     */
    public function toMatch(string $expression, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toMatchConstraint()` assertion for the previous expectation value
     */
    public function toMatchConstraint(Constraint $constraint, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toContainOnlyInstancesOf()` assertion for the previous expectation value
     */
    public function toContainOnlyInstancesOf(string $class, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }

    /**
     * Register a `toThrow()` assertion for the previous expectation value
     */
    public function toThrow(callable|string $exception, string $exceptionMessage = null, string $message = ''): static
    {
        return $this->registerExpectationMethod(__FUNCTION__, func_get_args());
    }
}
