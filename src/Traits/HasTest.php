<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Builder;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Exceptions\TestFunctionNotFoundException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Config;
use BradieTilley\StoryBoard\Testing\Timer\TimerUpException;
use Closure;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Throwable;

trait HasTest
{
    protected bool $inherited = false;

    protected bool $registered = false;

    protected bool $booted = false;

    protected ?TestCase $test = null;

    protected static string $testFunction = 'test';

    /**
     * Register this story actions
     */
    public function register(): static
    {
        $this->inherit();

        if ($this->skipDueToIsolation()) {
            return $this;
        }

        if ($this->alreadyRun('register')) {
            // @codeCoverageIgnoreStart
            return $this;
            // @codeCoverageIgnoreEnd
        }

        $this->registerActions();
        $this->registerTags();

        return $this;
    }

    /**
     * Boot (and register) the story and its actions
     */
    public function boot(): static
    {
        $this->register();

        if ($this->skipDueToIsolation()) {
            return $this;
        }

        if ($this->alreadyRun('boot')) {
            // @codeCoverageIgnoreStart
            return $this;
            // @codeCoverageIgnoreEnd
        }

        $this->bootPendingContext();
        $this->bootTestCaseShortcuts();
        $this->bootActions();

        return $this;
    }

    /**
     * Get the test case used for this story
     */
    public function getTest(): ?TestCase
    {
        return $this->test;
    }

    /**
     * Set the test case used for this story
     */
    public function setTest(TestCase $test): static
    {
        $this->test = $test;

        return $this;
    }

    /**
     * Create test cases for all tests
     */
    public function test(): static
    {
        if (! Builder::hasRun()) {
            Builder::run();
        }

        if (! $this->hasStories()) {
            return $this->testSingle();
        }

        if (Config::datasetsEnabled()) {
            $function = Story::getTestFunction();
            $parentName = $this->getName();
            $stories = $this->allStories();

            if (! is_callable($function)) {
                throw StoryBoardException::testFunctionNotFound($function);
            }

            $function($parentName, function (Story $story) {
                /** @var Story $story */
                /** @var TestCase $this */

                // @codeCoverageIgnoreStart
                $story->setTest($this)->boot()->perform();
                // @codeCoverageIgnoreEnd
            })->with($stories);
        } else {
            foreach ($this->allStories() as $story) {
                $story->test();
            }
        }

        return $this;
    }

    /**
     * Create a test case for this story (e.g. create a `test('name', fn () => ...)`)
     */
    public function testSingle(): static
    {
        $story = $this;

        $function = self::getTestFunction();
        $args = [
            $this->getTestName(),
            function () use ($story) {
                /** @var Story $story */
                /** @var TestCase $this */
                $story->setTest($this)->run();
            },
        ];

        /**
         * Pest uses a Backtrace class which expects the most recent backtrace items
         * to each include a file. By running call_user_func we lose the 'file' in the
         * relevant backtrace and therefore Pest cannot operate. So instead we'll call
         * the function directly. Not super nice, but hey.
         */
        if (! is_callable($function)) {
            throw StoryBoardException::testFunctionNotFound($function);
        }

        $function(...$args);

        return $this;
    }

    /**
     * Get the name of this test
     */
    public function getTestName(): string
    {
        $name = $this->getFullName();

        /**
         * Only the most lowest level story should get prefixed with can or cannot
         */
        if (! $this->hasStories()) {
            if ($this->can !== null) {
                $can = $this->can ? 'Can' : 'Cannot';

                $name = "[{$can}] {$name}";
            }

            if ($this->appendTags) {
                $tags = trim($this->getTagsAsName());

                if ($tags !== '') {
                    $name = trim("{$name} | {$tags}");
                }
            }
        }

        return $name;
    }

    /**
     * Set the name of the function that powers the testing. Default: `test`

     *
     * @throws TestFunctionNotFoundException
     */
    public static function setTestFunction(string $function = 'test'): void
    {
        if (! function_exists($function)) {
            throw StoryBoardException::testFunctionNotFound($function);
        }

        self::$testFunction = $function;
    }

    /**
     * Get the name of the function that powers the testing. Default: `test`
     */
    public static function getTestFunction(): string
    {
        return self::$testFunction;
    }

    /**
     * Inherit all properties that are inheritable
     */
    public function inherit(): static
    {
        if ($this->alreadyRun('inherited')) {
            // @codeCoverageIgnoreStart
            return $this;
            // @codeCoverageIgnoreEnd
        }

        $this->inheritIsolation();

        if ($this->skipDueToIsolation()) {
            return $this;
        }

        $this->inheritName();
        $this->inheritData();
        $this->inheritTags();
        $this->inheritActions();
        $this->inheritAssertions();
        $this->inheritCallbacks();
        $this->inheritTimeout();
        $this->inheritTestCaseShortcuts();
        $this->inheritPendingContext();

        return $this;
    }

    /**
     * Inherit assertions from ancestord
     */
    public function inheritAssertions(): void
    {
        $can = $this->inheritPropertyBool('can');

        if ($can !== null) {
            $this->can = $can;
        }
    }

    /**
     * Run this story from start to finish
     */
    public function run(): static
    {
        try {
            if ($this->timeoutEnabled && $this->timeout > 0) {
                $this->timer = $this->createTimer(fn () => $this->fullRun());
                $this->timer->run();
            } else {
                $this->fullRun();
            }
        } catch (TimerUpException $e) {
            $taken = $e->getTimeTaken();
            $timeout = $e->getTimeout();
            $timeoutFormatted = $e->getTimeoutFormatted();
            $message = "Failed asserting that this story would complete in less than {$timeoutFormatted}.";

            Assert::assertLessThanOrEqual(
                expected: $timeout,
                actual: $taken,
                message: $message,
            );

            /**
             * Fallback to rethrowing the exception
             */
            // @codeCoverageIgnoreStart
            throw $e;
            // @codeCoverageIgnoreEnd
        }

        return $this;
    }

    /**
     * Run the setUp callback
     */
    public function runSetUp(): void
    {
        if ($this->alreadyRun('setUp')) {
            return;
        }

        $this->runCallback('setUp', $this->getParameters());
    }

    /**
     * Run the tearDown callback
     */
    public function runTearDown(array $args = []): void
    {
        if ($this->alreadyRun('tearDown')) {
            return;
        }

        $this->runCallback('tearDown', $args);
    }

    /**
     * Run the full test assertion (after setTest)
     */
    public function fullRun(): static
    {
        $this->boot();
        $this->runSetUp();

        $args = [];

        try {
            $this->perform();
        } catch (Throwable $e) {
            $args = [
                'e' => $e,
                'exception' => $e,
            ];
        }

        $this->runTearDown($args);

        if (isset($e)) {
            throw $e;
        }

        return $this;
    }

    /**
     * Register a callback to run when the test is set up
     */
    public function setUp(?Closure $callback): static
    {
        return $this->setCallback('setUp', $callback);
    }

    /**
     * Register a callback to run when the test the teared down
     */
    public function tearDown(?Closure $callback): static
    {
        return $this->setCallback('tearDown', $callback);
    }
}
