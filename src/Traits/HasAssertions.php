<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Enums\Expectation;
use BradieTilley\StoryBoard\Exceptions\InvalidMagicMethodHandlerException;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Assertion;
use BradieTilley\StoryBoard\Story\StoryAssertion;
use Closure;

/**
 * This object has assertions, expectations and assertions
 * You can define assertions that are grouped by an expectation
 * of "can", "cannot" or "always", and then depending on the
 * scenario at hand you can choose an expectation and have its
 * assertions used.
 *
 * @method static can(string|Closure|Assertion|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static cannot(string|Closure|Assertion|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static always(string|Closure|Assertion|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static static can(string|Closure|Assertion|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static static cannot(string|Closure|Assertion|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static static always(string|Closure|Assertion|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 *
 * @mixin \BradieTilley\StoryBoard\Story
 */
trait HasAssertions
{
    /**
     * All assertions stored as StoryAssertion objects, excluding
     * inheritance until story is registered.
     *
     * @var array<string,array<int,StoryAssertion>>
     */
    protected array $assertions = [
        Expectation::EXPECT_ALWAYS->value => [],
        Expectation::EXPECT_CAN->value => [],
        Expectation::EXPECT_CANNOT->value => [],
    ];

    /**
     * Current expectation of Can or Cannot
     */
    protected ?bool $expectation = null;

    /**
     * Flag that indicates that inheritance must halt at
     * this story in the 'family tree'. If '$expectation' is 'null'
     * here on this Story, we should not look any further.
     *
     * Set to true when resetExpectation() is run. This will override
     * a parent can/cannot and reset it back to null for this
     * story and its children.
     */
    protected bool $expectationHalt = false;

    /**
     * Method alias(es) for Assertions trait
     */
    public function __callAssertions(string $method, array $parameters): mixed
    {
        if ($expectation = Expectation::tryFrom($method)) {
            if ($expectation === Expectation::EXPECT_CAN || $expectation === Expectation::EXPECT_CANNOT) {
                $this->expectation = ($expectation === Expectation::EXPECT_CAN);
            }

            if (count($parameters)) {
                $assertion = $parameters[0];
                $arguments = $parameters[1] ?? [];
                $order = $parameters[2] ?? null;

                return $this->setAssertion(
                    assertion: $assertion,
                    arguments: $arguments,
                    order: $order,
                    expectation: $expectation,
                );
            }

            return $this;
        }

        // @codeCoverageIgnoreStart
        throw StoryBoardException::invalidMagicMethodHandlerException($method, InvalidMagicMethodHandlerException::TYPE_METHOD);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Static method alias(es) for Assertions trait
     */
    public static function __callStaticAssertions(string $method, array $parameters): mixed
    {
        if ($method === Expectation::EXPECT_ALWAYS->value || $method === Expectation::EXPECT_CAN->value || $method === Expectation::EXPECT_CANNOT->value) {
            return static::make()->{$method}(...$parameters);
        }

        // @codeCoverageIgnoreStart
        throw StoryBoardException::invalidMagicMethodHandlerException($method, InvalidMagicMethodHandlerException::TYPE_STATIC_METHOD);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Alias for setAssertion()
     */
    public function assertion(string|Closure|Assertion $assertion, array $arguments = [], int $order = null, Expectation $expectation = null): static
    {
        return $this->setAssertion($assertion, $arguments, $order, $expectation);
    }

    /**
     * Register a single assertion for this story.
     * Optionally pass in arguments (matched by name) if the assertion supports them.
     */
    public function setAssertion(string|Closure|Assertion $assertion, array $arguments = [], int $order = null, Expectation $expectation = null): static
    {
        assert($this instanceof Story);

        $assertion = Assertion::prepare($assertion);
        $key = $expectation ? $expectation->value : $this->getCurrentExpectationKey();

        $this->assertions[$key][] = new StoryAssertion(
            story: $this,
            assertion: $assertion,
            arguments: $arguments,
            order: $order,
        );

        return $this;
    }

    /**
     * Get the default expectation key to append assertions to.
     */
    public function getCurrentExpectationKey(): string
    {
        return match ($this->expectation) {
            null => Expectation::EXPECT_ALWAYS->value,
            true => Expectation::EXPECT_CAN->value,
            false => Expectation::EXPECT_CANNOT->value,
        };
    }

    /**
     * Alias for setAssertions()
     */
    public function assertions(iterable $assertions, Expectation $expectation = null): static
    {
        return $this->setAssertions($assertions, expectation: $expectation);
    }

    /**
     * Register multiple assertions for this story.
     *
     * The order of each assertion is inherited from the assertions themselves.
     */
    public function setAssertions(iterable $assertions, Expectation $expectation = null): static
    {
        foreach ($assertions as $assertion => $arguments) {
            // Closures and classes will be int key
            if (is_string($arguments) || ($arguments instanceof Closure) || ($arguments instanceof Assertion)) {
                $assertion = $arguments;
                $arguments = [];
            }

            $this->setAssertion($assertion, $arguments, expectation: $expectation);
        }

        return $this;
    }

    /**
     * Get all regsitered assertions for this story (no inheritance lookup)
     *
     * @return array<string,array<int,StoryAssertion>>
     */
    public function getAssertions(): array
    {
        return $this->assertions;
    }

    /**
     * Define the given assertions for can cannot or any scenario
     */
    public function assert(
        string|Closure|Assertion $can = null,
        string|Closure|Assertion $cannot = null,
        string|Closure|Assertion $always = null,
    ): static {
        if ($can !== null) {
            $this->whenCan($can);
        }

        if ($cannot !== null) {
            $this->whenCannot($cannot);
        }

        if ($always !== null) {
            $this->whenAlways($always);
        }

        return $this;
    }

    /**
     * Assert that when this is flagged as `can()` that the given assertion
     * will pass.
     */
    public function whenCan(string|Closure|Assertion $assertion): static
    {
        $this->setAssertion($assertion, expectation: Expectation::EXPECT_CAN);

        return $this;
    }

    /**
     * Assert that when this is flagged as `cannot()` that the given assertion
     * will pass.
     */
    public function whenCannot(string|Closure|Assertion $assertion): static
    {
        $this->setAssertion($assertion, expectation: Expectation::EXPECT_CANNOT);

        return $this;
    }

    /**
     * Assert that this given assertion will always pass
     */
    public function whenAlways(string|Closure|Assertion $assertion): static
    {
        $this->setAssertion($assertion, expectation: Expectation::EXPECT_ALWAYS);

        return $this;
    }

    /**
     * Reset the expectation
     */
    public function resetExpectation(): static
    {
        $this->expectation = null;
        $this->expectationHalt = true;

        return $this;
    }

    /**
     * Get the 'can' / 'cannot' flag for this story
     */
    public function itCan(): ?bool
    {
        return $this->expectation;
    }

    /**
     * Inherit assertions from ancestors
     */
    public function inheritAssertions(): void
    {
        $expectation = $this->inheritPropertyBool('expectation');

        if ($expectation !== null) {
            $this->expectation = $expectation;
        }

        $all = [
            Expectation::EXPECT_CAN->value => [],
            Expectation::EXPECT_CANNOT->value => [],
            Expectation::EXPECT_ALWAYS->value => [],
        ];

        $keys = [
            $this->getCurrentExpectationKey(),
            Expectation::EXPECT_ALWAYS->value,
        ];

        $keys = array_unique($keys);

        foreach ($this->getAncestors() as $ancestor) {
            if ($ancestor->getProperty('expectationHalt') === true) {
                break;
            }

            foreach ($keys as $key) {
                foreach ($ancestor->assertions[$key] as $storyAssertion) {
                    $all[$key][] = (clone $storyAssertion)->withStory($this);
                }
            }
        }

        $this->assertions = $all;
    }
}