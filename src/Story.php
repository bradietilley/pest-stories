<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Contracts\InvokableCallback;
use BradieTilley\Stories\Exceptions\FunctionAliasNotFoundException;
use BradieTilley\Stories\Exceptions\TestCaseUnavailableException;
use BradieTilley\Stories\Helpers\StoryAliases;
use BradieTilley\Stories\Helpers\VariableNaming;
use BradieTilley\Stories\Traits\Conditionable;
use BradieTilley\Stories\Traits\TestCallMethods;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Pest\Expectation;
use Pest\PendingCalls\TestCall;
use Pest\TestSuite;
use PHPUnit\Framework\TestCase;
use Tests\Mocks\PestStoriesMockTestCall;

/**
 * @method static static make(string $name = '', ?Closure $callback = null, array $arguments = [])
 */
class Story extends Callback
{
    use Conditionable;
    use Macroable;
    use TestCallMethods;

    /** @var array<Story> */
    protected array $stories = [];

    /** @var array<int,array<string,string|array>> Key = name; Value = arguments */
    protected array $actions = [];

    /** @var array<int,array<string,string|array>> Key = name; Value = arguments */
    protected array $assertions = [];

    /** @var array<Closure> */
    protected array $setUp = [];

    /** @var array<Closure> */
    protected array $tearDown = [];

    protected false|string $incomplete = false;

    protected array $proxies = [];

    protected array $appends = [];

    protected ExpectationChain $expectations;

    public function __construct(string $name = '', ?Closure $callback = null, array $arguments = [])
    {
        parent::__construct($name, $callback, $arguments);

        $this->testCalls = InvocationQueue::make();
        $this->conditionables = InvocationQueue::make();
        $this->expectations = ExpectationChain::make();
        $this->variable = 'result';
    }

    /**
     * Get the key used to find the aliased class
     */
    public static function getAliasKey(): string
    {
        return 'story';
    }

    /**
     * Add a child story to run when this story runs
     *
     * @param  array<string|Story>|string|Story  $story
     */
    public function stories(array|string|Story|ExpectationChain $story, array $arguments = []): static
    {
        $story = (is_array($story)) ? $story : [$story];

        $story = array_map(
            function (string|Story|ExpectationChain $story) {
                $story = ($story instanceof ExpectationChain) ? $story->story() : $story;
                $story = (is_string($story)) ? Story::fetch($story) : $story;

                return $story;
            },
            $story,
        );

        foreach ($story as $storyItem) {
            $this->stories[] = $storyItem->with($arguments);
        }

        return $this;
    }

    /**
     * Are there child stories?
     */
    public function hasStories(): bool
    {
        return ! empty($this->stories);
    }

    /**
     * @return array<Story>
     */
    public function getStories(): array
    {
        return $this->stories;
    }

    /**
     * Add an action for this story
     *
     * @param  array<string|Action|Closure>|string|Action|Closure  $action
     */
    public function action(array|string|Action|Closure $action, array $arguments = [], string $for = null): static
    {
        $closureBased = $action instanceof Closure;

        $action = (is_array($action)) ? $action : [$action];

        $action = array_map(
            fn (string|Action|Closure $action): Action => Action::prepare($action),
            $action,
        );

        foreach ($action as $actionItem) {
            if ($closureBased && ($for !== null)) {
                $actionItem->for($for);
            }

            /**
             * Invokable actions may not be stored in the repostory yet so
             * record the callback so that we can reference the callback by
             * its name.
             */
            if ($actionItem instanceof InvokableCallback) {
                $actionItem->store();
            }

            $this->actions[] = [
                'name' => $actionItem->getName(),
                'arguments' => $arguments,
            ];
        }

        return $this;
    }

    /**
     * Get all actions that have been added to the story
     *
     * @return array<int,array<string,string|array>>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Add an expectation for this story
     *
     * @param  array<string|Assertion|Closure>|string|Assertion|Closure  $assertion
     */
    public function assertion(array|string|Assertion|Closure $assertion, array $arguments = []): static
    {
        $assertion = (is_array($assertion)) ? $assertion : [$assertion];

        $assertion = array_map(
            fn (string|Assertion|Closure $assertion): Assertion => Assertion::prepare($assertion),
            $assertion,
        );

        foreach ($assertion as $assertionItem) {
            /**
             * Invokable assertions may not be stored in the repostory yet
             * so record the callback so that we can reference the callback
             * by its name.
             */
            if ($assertionItem instanceof InvokableCallback) {
                $assertionItem->store();
            }

            $this->assertions[] = [
                'name' => $assertionItem->getName(),
                'arguments' => $arguments,
            ];
        }

        return $this;
    }

    /**
     * Get all assertions that have been added to the story
     *
     * @return array<int,array<string,string|array>>
     */
    public function getAssertions(): array
    {
        return $this->assertions;
    }

    /**
     * Get all child/grandchild/etc stories
     *
     * @return Collection<int, Story>
     */
    public function flattenStories(): Collection
    {
        /** @var Collection<int, Story> $stories */
        $stories = Collection::make([]);

        foreach ($this->getStories() as $story) {
            $story->internalInherit($this);

            if ($story->hasStories()) {
                foreach ($story->flattenStories() as $subStory) {
                    $stories[] = $subStory;
                }

                continue;
            }

            $stories[] = $story;
        }

        return $stories;
    }

    /**
     * Register this story via Pest's `test()` function
     */
    public function test(): TestCall|PestStoriesMockTestCall
    {
        $dataset = null;
        $parentName = $this->getName();

        if ($this->hasStories()) {
            $dataset = [];

            foreach ($this->flattenStories() as $story) {
                $storyName = $story->getTestName();
                $datasetName = trim(substr($storyName, strlen($parentName)));

                $dataset[$datasetName] = [$story];
            }
        }

        $function = StoryAliases::getFunction('test');

        if (! function_exists($function)) {
            // @codeCoverageIgnoreStart
            throw FunctionAliasNotFoundException::make('test', $function);
            // @codeCoverageIgnoreEnd
        }

        /** @var TestCall|PestStoriesMockTestCall $testCall */
        $testCall = $function($this->getTestName(), $this->getTestCallback());

        if ($dataset !== null) {
            $testCall->with($dataset);
        }

        $this->applyTestCalls($testCall);

        return $testCall;
    }

    /**
     * Get the Closure callback to run in Pest's test() function
     */
    public function getTestCallback(): Closure
    {
        if ($this->hasStories()) {
            return function (Story $story) {
                $story->process();
            };
        }

        /** @var Story $story */
        $story = $this;

        return function () use ($story) {
            $story->process();
        };
    }

    /**
     * Get the current test case instance
     */
    protected function getTestCase(): TestCase
    {
        $test = TestSuite::getInstance()->test;

        if ($test === null) {
            // @codeCoverageIgnoreStart
            throw TestCaseUnavailableException::make($this);
            // @codeCoverageIgnoreEnd
        }

        return $test;
    }

    /**
     * Run the story under the given test suite.
     *
     * @param  array  $arguments Not used for stories
     */
    public function process(array $arguments = []): mixed
    {
        $alarm = $this->alarm();

        /**
         * If a time limit was provided, start the alarm
         */
        if ($alarm) {
            $alarm->start();
        }

        /**
         * Retrieve the TestCase so the story can easily reference it.
         */
        $test = $this->getTestCase();
        $arguments = [
            'test' => $test,
        ];
        $this->with($arguments);

        /**
         * Set Up
         */
        foreach ($this->setUp as $callback) {
            $this->internalCall($callback);
        }

        /**
         * Boot Conditionables
         */
        $this->internalBootConditionables();

        /**
         * Actions (Setup the scenario)
         */
        foreach ($this->getActions() as $data) {
            /** @var string $name */
            $name = $data['name'];
            /** @var array $arguments */
            $arguments = $data['arguments'];

            // Use story variables and action arguments
            $arguments = array_replace(
                $this->with,
                [
                    'story' => $this,
                ],
                $arguments,
            );

            $action = clone Action::fetch($name);
            $variable = $action->getVariable();

            $value = $action->process($arguments);
            $this->set($variable, $value);
        }

        /**
         * Custom story logic
         */
        $key = $this->getVariable();
        $value = parent::boot();
        $arguments[$key] = $value;
        $this->set($key, $value);

        /**
         * Pest Expectations
         */
        $this->bootChain();

        /**
         * Custom assertions
         */
        foreach ($this->getAssertions() as $data) {
            /** @var string $name */
            $name = $data['name'];
            /** @var array $arguments */
            $arguments = $data['arguments'];

            // Use story variables and action arguments
            $arguments = array_replace(
                $this->with,
                [
                    'story' => $this,
                ],
                $arguments,
            );

            $assertion = clone Assertion::fetch($name);
            $variable = $assertion->getVariable();

            $value = $assertion->process($arguments);
            $this->set($variable, $value);
        }

        /**
         * Tear Down
         */
        foreach ($this->tearDown as $callback) {
            $this->internalCall($callback);
        }

        /**
         * If an alarm is tracking the time, stop it and throw if we exceeded the limit
         */
        if ($alarm) {
            $alarm->stop();
        }

        return $this;
    }

    /**
     * Inherit actions, assertions and name from the given parent
     */
    public function internalInherit(Story $parent): static
    {
        /**
         * Collate actions and assertions from the parent as well as from this story
         */
        $this->actions = collect($parent->getPropertyArray('actions'))
            ->concat($this->getPropertyArray('actions'))
            ->all();
        $this->assertions = collect($parent->getPropertyArray('assertions'))
            ->concat($this->getPropertyArray('assertions'))
            ->all();

        /**
         * Concatenate the parent story's name fragment (if any) with this story's
         * name fragment (if any) to produce a more fully qualified name.
         */
        $this->name = trim(sprintf('%s %s', $parent->getName(), $this->getName()));

        /**
         * Use this story's primary callback if defined, otherwise inherit it from
         * the parent if defined.
         */
        $this->callback ??= $parent->getCallback();

        /**
         * Inherit all TestCall proxied methods from the parent and this story.
         */
        $this->testCalls->inherit($parent->testCalls);

        /**
         * Collate the before and after callbacks from the parent as well as from
         * this story, with the parent's callbacks being listed first before this
         * story's callbacks.
         */
        $this->before = collect($parent->getPropertyArray('before'))
            ->concat($this->getPropertyArray('before'))
            ->all();
        $this->after = collect($parent->getPropertyArray('after'))
            ->concat($this->getPropertyArray('after'))
            ->all();

        /**
         * Collate the setUp and tearDown callbacks from the parent as well as from
         * this story
         */
        $this->setUp = collect($parent->getPropertyArray('setUp'))
            ->concat($this->getPropertyArray('setUp'))
            ->all();
        $this->tearDown = collect($parent->getPropertyArray('tearDown'))
            ->concat($this->getPropertyArray('tearDown'))
            ->all();

        /**
         * Merge conditionable when/unless callbacks from the parent as well as from
         * this story.
         */
        $this->conditionables->inherit($parent->conditionables);

        /**
         * Merge variables
         */
        $this->with = collect($parent->getPropertyArray('with'))
            ->replace($this->getPropertyArray('with'))
            ->all();

        /**
         * Merge appendable variables
         */
        $this->appends = collect($parent->getPropertyArray('appends'))
            ->concat($this->getPropertyArray('appends'))
            ->all();

        $this->expectations->inherit($parent->chain());

        return $this;
    }

    /**
     * Add a callback to run immediately when the TestCase is
     * assigned to the Story isntance
     */
    public function setUp(Closure $callback): static
    {
        $this->setUp[] = $callback;

        return $this;
    }

    /**
     * Add a callback to run immediately before the TestCase is
     * about to be teared down
     */
    public function tearDown(Closure $callback): static
    {
        $this->tearDown[] = $callback;

        return $this;
    }

    /**
     * Append the given variable to the story name.
     *
     * Note: the variables must be available before the story is
     * booted in order for the variables to be appended. Those
     * variables that are set during the boot process of a story
     * will be unavailable before boot and won't be added.
     */
    public function appends(string $variable): static
    {
        $this->appends[] = $variable;

        return $this;
    }

    /**
     * Get the name of this callback, action, assertion or story
     */
    public function getTestName(): string
    {
        if ($this->hasStories()) {
            return $this->getName();
        }

        $name = [];

        foreach ($this->appends as $variable) {
            $value = $this->get($variable);

            $name[] = "{$variable}: ".VariableNaming::stringify($value);
        }

        $name = sprintf(
            '%s %s',
            $this->getName(),
            implode(', ', $name),
        );

        return trim($name);
    }

    /**
     * Specify the variable name (string) or value (Closure return)
     * to use in subsequent expectations.
     */
    public function expect(string|Closure $value): ExpectationChain
    {
        $this->expectations->setStory($this);
        $this->expectations->and($value);

        return $this->expectations;
    }

    /**
     * Get expectations
     */
    public function chain(): ExpectationChain
    {
        return $this->expectations;
    }

    /**
     * Execute any chained expectation pest expectation
     * calls, originally called from $story->expect('var')->toBe(X)...
     */
    public function bootChain(): void
    {
        if ($this->expectations->queue->isEmpty()) {
            return;
        }

        /** @var ?Expectation $expectation */
        $expectation = null;

        foreach ($this->expectations->queue->items as $invocation) {
            if ($invocation->isFunction()) {
                /**
                 * Any function here is either 'expect' or a custom replacement of 'expect'.
                 *
                 * First we resolve and swap out the argument to the expect() function as this
                 * is currently a variable name (string) in which case we need to fetch the
                 * variable by name, or it's currently a callback (Closure) in which case we
                 * need to invoke it to use the return value as the expectation value.
                 */

                /** @var string|Closure $value */
                $value = $invocation->arguments[0];
                $value = (is_string($value)) ? $this->get($value) : $this->internalCall($value);

                /** @var Expectation $expectation */
                $invocation->setArguments([$value]);
            }

            /**
             * The first invocation is always the 'expect' function (or alias) so at this
             * stage it would be null, but every invocation afterwards SHOULD be an instance
             * of an Expectation.
             *
             * @var ?Expectation $expectation
             */

            /**
             * Either invoke the function, invoke the method on the Expectation object, or
             * get the property from the Expectation object.
             */
            $expectation = $invocation->setObject($expectation)->invoke();

            /**
             * It should always be an Expecation now.
             *
             * @var Expectation $expectation
             */
        }
    }
}
