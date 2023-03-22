<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Exceptions\FunctionAliasNotFoundException;
use BradieTilley\Stories\Exceptions\TestCaseUnavailableException;
use BradieTilley\Stories\Helpers\StoryAliases;
use BradieTilley\Stories\Traits\Conditionable;
use BradieTilley\Stories\Traits\TestCallProxies;
use Closure;
use Illuminate\Support\Traits\Macroable;
use Pest\PendingCalls\TestCall;
use Pest\TestSuite;
use PHPUnit\Framework\TestCase;
use Tests\Mocks\PestStoriesMockTestCall;

class Story extends Callback
{
    use Conditionable;
    use Macroable;
    use TestCallProxies;

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

    protected bool $todo = false;

    public function __construct(protected string $name, protected ?Closure $callback = null, array $arguments = [])
    {
        parent::__construct($name, $callback, $arguments);

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
    public function stories(array|string|Story $story, array $arguments = []): static
    {
        $story = (is_array($story)) ? $story : [$story];

        $story = array_map(
            fn (string|Story $story): Story => (is_string($story)) ? Story::fetch($story) : $story,
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
    public function action(array|string|Action|Closure $action, array $arguments = []): static
    {
        $action = (is_array($action)) ? $action : [$action];

        $action = array_map(
            fn (string|Action|Closure $action): Action => Action::prepare($action),
            $action,
        );

        foreach ($action as $actionItem) {
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
     * @return array<Story>
     */
    public function flattenStories(): array
    {
        $stories = [];

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
                $storyName = $story->getName();
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
        $testCall = $function($this->getName(), $this->getTestCallback());

        if ($dataset !== null) {
            $testCall->with($dataset);
        }

        $this->applyTestCallProxies($testCall);

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
         * Expectations and assertions
         */
        foreach ($this->getAssertions() as $data) {
            /** @var string $name */
            $name = $data['name'];
            /** @var array $arguments */
            $arguments = $data['arguments'];

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
         *
         */
        $testCallProxies = $parent->getPropertyArray('testCallProxies');
        foreach ($this->testCallProxies as $method => $invokations) {
            foreach ($invokations as $arguments) {
                $testCallProxies[$method] ??= [];
                $testCallProxies[$method][] = $arguments;
            }
        }
        $this->testCallProxies = $testCallProxies;

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
        $this->conditionables = collect($parent->getPropertyArray('conditionables'))
            ->concat($this->getPropertyArray('conditionables'))
            ->all();

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
}
