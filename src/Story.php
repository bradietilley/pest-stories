<?php

namespace BradieTilley\StoryBoard;

use BradieTilley\StoryBoard\Contracts\WithActions;
use BradieTilley\StoryBoard\Contracts\WithCallbacks;
use BradieTilley\StoryBoard\Contracts\WithData;
use BradieTilley\StoryBoard\Contracts\WithInheritance;
use BradieTilley\StoryBoard\Contracts\WithIsolation;
use BradieTilley\StoryBoard\Contracts\WithName;
use BradieTilley\StoryBoard\Contracts\WithNameShortcuts;
use BradieTilley\StoryBoard\Contracts\WithPendingContext;
use BradieTilley\StoryBoard\Contracts\WithPerformer;
use BradieTilley\StoryBoard\Contracts\WithSingleRunner;
use BradieTilley\StoryBoard\Contracts\WithStories;
use BradieTilley\StoryBoard\Contracts\WithTags;
use BradieTilley\StoryBoard\Contracts\WithTestCaseShortcuts;
use BradieTilley\StoryBoard\Contracts\WithTimeout;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Exceptions\TestFunctionNotFoundException;
use BradieTilley\StoryBoard\Story\Config;
use BradieTilley\StoryBoard\Testing\Timer\TimerUpException;
use BradieTilley\StoryBoard\Traits\HasActions;
use BradieTilley\StoryBoard\Traits\HasCallbacks;
use BradieTilley\StoryBoard\Traits\HasData;
use BradieTilley\StoryBoard\Traits\HasInheritance;
use BradieTilley\StoryBoard\Traits\HasIsolation;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasNameShortcuts;
use BradieTilley\StoryBoard\Traits\HasPendingContext;
use BradieTilley\StoryBoard\Traits\HasPerformer;
use BradieTilley\StoryBoard\Traits\HasSingleRunner;
use BradieTilley\StoryBoard\Traits\HasStories;
use BradieTilley\StoryBoard\Traits\HasTags;
use BradieTilley\StoryBoard\Traits\HasTestCaseShortcuts;
use BradieTilley\StoryBoard\Traits\HasTimeout;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @property-read Collection<int,Story> $storiesDirect
 * @property-read Collection<string,Story> $storiesAll
 * @property-read ?Authenticatable $user
 *
 * @method self can(string|Closure|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method self cannot(string|Closure|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static self can(string|Closure|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 * @method static self cannot(string|Closure|null $name = null, string|Closure|null $assertion = null) Named arguments not supported (magic)
 */
class Story implements WithActions, WithCallbacks, WithData, WithInheritance, WithIsolation, WithName, WithNameShortcuts, WithPendingContext, WithPerformer, WithSingleRunner, WithStories, WithTimeout, WithTags, WithTestCaseShortcuts
{
    use Conditionable;
    use HasCallbacks;
    use HasData;
    use HasName;
    use HasNameShortcuts;
    use HasInheritance;
    use HasIsolation;
    use HasPendingContext;
    use HasPerformer;
    use HasActions;
    use HasSingleRunner;
    use HasStories;
    use HasTags;
    use HasTestCaseShortcuts;
    use HasTimeout;
    use Macroable {
        __call as __callMacroable;
        __callStatic as __callStaticMacroable;
    }

    public readonly int $id;

    private static int $idCounter = 0;

    protected bool $inherited = false;

    protected bool $registered = false;

    protected bool $booted = false;

    protected ?TestCase $test = null;

    protected static string $testFunction = 'test';

    public function __construct(protected ?string $name = null, protected ?Story $parent = null)
    {
        $this->id = ++self::$idCounter;
    }

    /**
     * Proxy certain property getters to methods
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        if ($name === 'storiesDirect') {
            return $this->collectGetStories();
        }

        if ($name === 'storiesAll') {
            return $this->collectAllStories();
        }

        if ($name === 'user') {
            return $this->getUser();
        }

        return $this->{$name};
    }

    /**
     * Proxy the can/cannot methods to their setters
     *
     * @param  string  $method
     * @param  array<mixed>  $parameters
     */
    public function __call($method, $parameters): mixed
    {
        if ($method === 'can' || $method === 'cannot') {
            $method = 'set'.ucfirst($method);

            return $this->{$method}(...$parameters);
        }

        return $this->__callMacroable($method, $parameters);
    }

    /**
     * Proxy the can/cannot methods to their setters
     *
     * @param  string  $method
     * @param  array<mixed>  $parameters
     */
    public static function __callStatic($method, $parameters): mixed
    {
        if ($method === 'can' || $method === 'cannot') {
            return self::make()->{$method}(...$parameters);
        }

        return static::__callStaticMacroable($method, $parameters);
    }

    /**
     * Create a new story
     */
    public static function make(?string $name = null, ?Story $parent = null): static
    {
        /** @phpstan-ignore-next-line */
        return new static($name, $parent);
    }

    /**
     * Get parameters available for DI callbacks
     *
     * @return array
     */
    public function getParameters(array $additional = []): array
    {
        $data = array_replace($this->allData(), [
            'story' => $this,
            'test' => $this->getTest(),
            'can' => $this->can,
            'user' => $this->getUser(),
            'result' => $this->getResult(),
        ], $additional);

        return $data;
    }

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
            return $this->testSingleStory();
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
    private function testSingleStory(): static
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
    protected function inheritAssertions(): void
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
    private function runSetUp(): void
    {
        if ($this->alreadyRun('setUp')) {
            return;
        }

        $this->runCallback('setUp', $this->getParameters());
    }

    /**
     * Run the tearDown callback
     */
    private function runTearDown(array $args = []): void
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
