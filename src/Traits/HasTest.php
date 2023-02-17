<?php

namespace BradieTilley\StoryBoard\Traits;

use BradieTilley\StoryBoard\Contracts\ExpectsThrows;
use BradieTilley\StoryBoard\Contracts\WithTestCaseShortcuts;
use function BradieTilley\StoryBoard\debug;
use BradieTilley\StoryBoard\Enums\StoryStatus;
use function BradieTilley\StoryBoard\error;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Config;
use BradieTilley\StoryBoard\StoryApplication;
use BradieTilley\StoryBoard\Testing\Timer\TimerUpException;
use Closure;
use Pest\PendingObjects\TestCall;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\RiskyTestError;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * This object (story) has facility to register a test via
 * the `->test()` method. Once the test is registered and Pest
 * invokes it, the TestCase can be provided via the `->setTest()`
 * method and later retrieved via `->getTest()`.
 *
 * Therefore, during the registration/creation of a Story test,
 * there is no TestCase available. Any even after registration
 * will have access to the TestCase.
 *
 * @mixin \BradieTilley\StoryBoard\Contracts\WithStories
 */
trait HasTest
{
    /**
     * This is the TestCase responsible for running the Story.
     *
     * Accessible only when Pest boots the test created via `->test()`
     */
    protected ?TestCase $test = null;

    /**
     * The status of the story's test case
     */
    protected StoryStatus $status = StoryStatus::PENDING;

    /**
     * Register this story actions
     */
    public function register(): static
    {
        /**
         * If this story has children then it should not be inherited; instead,
         * each of its children should run the `->register()` method.
         */
        if ($this->hasStories()) {
            $this->collectAllStories()->each(fn (Story $story) => $story->register());

            return $this;
        }

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
        /**
         * If this story has children then it should not be inherited; instead,
         * each of its children should run the `->boot()` method.
         */
        if ($this->hasStories()) {
            $this->collectAllStories()->each(fn (Story $story) => $story->boot());

            return $this;
        }

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
        StoryApplication::boot();

        if (! $this->hasStories()) {
            return $this->testSingle();
        }

        if (Config::datasetsEnabled()) {
            debug('Datasets enabled');

            $function = Config::getAliasFunction('test');

            debug(
                sprintf('Test function resolved as `%s()`', $function),
            );

            $parentName = $this->getName();
            $stories = $this->allStories();

            $testCall = $function($parentName, function (Story $story) {
                /** @var Story $story */
                /** @var TestCase $this */

                // @codeCoverageIgnoreStart
                $story->setTest($this)->boot()->perform();
                // @codeCoverageIgnoreEnd
            })->with($stories);

            if ($this instanceof WithTestCaseShortcuts) {
                if ($testCall instanceof TestCall || $testCall instanceof ExpectsThrows) {
                    $this->forwardTestCaseShortcutsToTestCall($testCall);
                }
            }
        } else {
            debug('Datasets disabled');

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
        /** @var Story $this */
        $this->assignDebugContainer();

        $story = $this;

        $function = Config::getAliasFunction('test');

        debug(
            sprintf('Test function resolved as `%s()`', $function),
        );

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
        $testCall = $function(...$args);

        if ($this instanceof WithTestCaseShortcuts) {
            if ($testCall instanceof TestCall || $testCall instanceof ExpectsThrows) {
                $this->forwardTestCaseShortcutsToTestCall($testCall);
            }
        }

        /** @phpstan-ignore-next-line */
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
     * Inherit all properties that are inheritable
     */
    public function inherit(): static
    {
        debug('Inheriting from parent stories');

        $this->status = StoryStatus::RUNNING;

        /**
         * If this story has children then it should not be inherited; instead,
         * each of its children should run the `->inherit()` method.
         */
        if ($this->hasStories()) {
            $this->collectAllStories()->each(fn (Story $story) => $story->inherit());

            return $this;
        }

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
     * Run this story from start to finish
     */
    public function run(): static
    {
        debug('Test::run() start');

        try {
            if ($this->timeoutEnabled && $this->timeout > 0) {
                debug('Timeout enabled; running story via Timer');

                $this->timer = $this->createTimer(fn () => $this->fullRun());
                $this->timer->run();
            } else {
                debug('Timeout disabled, running story directly');

                $this->fullRun();
            }
        } catch (TimerUpException $e) {
            error('Test::run() timeout reached', $e);

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
        } catch (\Throwable $e) {
            error('Test::run() unexpected error', $e);

            $this->setStatusFromException($e);

            throw $e;
        }

        $this->status = StoryStatus::SUCCESS;

        debug('Test::run() success');

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
        debug('Running test');

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

            $this->setStatusFromException($e);
        }

        $this->runTearDown($args);

        if (isset($e)) {
            debug('Ran test with error', $e);

            throw $e;
        }

        debug('Ran test successfully');

        return $this;
    }

    private function setStatusFromException(Throwable $error): void
    {
        if ($error instanceof RiskyTestError) {
            $this->status = StoryStatus::RISKY;
        } elseif ($error instanceof IncompleteTestError) {
            $this->status = StoryStatus::INCOMPLETE;
        } elseif ($error instanceof SkippedTestError) {
            $this->status = StoryStatus::SKIPPED;
        } else {
            $this->status = StoryStatus::FAILURE;
        }
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

    /**
     * Get the status of the test
     */
    public function getStatus(): StoryStatus
    {
        return $this->status;
    }
}
