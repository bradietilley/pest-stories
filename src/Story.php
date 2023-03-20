<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Exceptions\FunctionAliasNotFoundException;
use BradieTilley\Stories\Helpers\StoryAliases;
use Closure;
use Pest\PendingCalls\TestCall;
use Pest\Support\HigherOrderTapProxy;
use PHPUnit\Framework\TestCase;
use Tests\Mocks\PestStoriesMockTestCall;

class Story extends Callback
{
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

    protected false|string $skipped = false;

    protected bool $todo = false;

    public function __construct(protected string $name, protected ?Closure $callback, array $arguments = [])
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
     * Register this story's Pest test function
     */
    public function register(): TestCall|PestStoriesMockTestCall
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
        $testCall = call_user_func($function, $this->getName(), $this->getTestCallback());

        if ($dataset !== null) {
            $testCall->with($dataset);
        }

        if ($this->isTodo()) {
            $testCall->todo();
        }

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
    public static function getTestCase(): TestCase
    {
        $test = test();
        assert($test instanceof HigherOrderTapProxy);

        return $test->target;
    }

    /**
     * Run the story under the given test suite.
     */
    public function process(): static
    {
        $test = static::getTestCase();

        $arguments = [
            'test' => $test,
        ];
        $this->with($arguments);

        foreach ($this->setUp as $callback) {
            $this->internalCall($callback);
        }

        if (is_string($this->skipped)) {
            $test->markTestSkipped($this->skipped);
        }

        if (is_string($this->incomplete)) {
            $test->markTestIncomplete($this->incomplete);
        }

        // 1: Setup scenario by running actions
        foreach ($this->getActions() as $data) {
            /** @var string $name */
            $name = $data['name'];
            /** @var array $arguments */
            $arguments = $data['arguments'];

            $action = clone Action::fetch($name);
            $variable = $action->getVariable();

            $value = $action->boot($arguments);
            $this->set($variable, $value);
        }

        // 2: Custom story logic
        $arguments[$this->getVariable()] = $this->boot();

        // 3: Expectations
        foreach ($this->getAssertions() as $data) {
            /** @var string $name */
            $name = $data['name'];
            /** @var array $arguments */
            $arguments = $data['arguments'];

            $assertion = clone Assertion::fetch($name);
            $variable = $assertion->getVariable();

            $value = $assertion->boot($arguments);
            $this->set($variable, $value);
        }

        foreach ($this->tearDown as $callback) {
            $this->internalCall($callback);
        }

        return $this;
    }

    /**
     * Inherit actions, assertions and name from the given parent
     */
    public function internalInherit(Story $parent): static
    {
        $this->actions = collect($parent->getActions())->concat($this->getActions())->all();
        $this->assertions = collect($parent->getAssertions())->concat($this->getAssertions())->all();
        $this->name = trim(sprintf('%s %s', $parent->getName(), $this->getName()));
        $this->callback ??= $parent->getCallback();
        $this->skipped = $this->isSkipped() ? $this->skipped : $parent->getSkipped();
        $this->incomplete = $this->isIncomplete() ? $this->incomplete : $parent->getIncomplete();

        return $this;
    }

    /**
     * Mark the test as incomplete
     */
    public function incomplete(string $message = ''): static
    {
        $this->incomplete = $message;

        return $this;
    }

    /**
     * Is this story marked as incomplete?
     */
    public function isIncomplete(): bool
    {
        return $this->incomplete !== false;
    }

    /**
     * Get the incomplete message
     */
    public function getIncomplete(): false|string
    {
        return $this->incomplete;
    }

    /**
     * Mark the test as skipped
     */
    public function skipped(string $message = ''): static
    {
        $this->skipped = $message;

        return $this;
    }

    /**
     * Is this story marked as skipped?
     */
    public function isSkipped(): bool
    {
        return $this->skipped !== false;
    }

    /**
     * Get the skipped message
     */
    public function getSkipped(): false|string
    {
        return $this->skipped;
    }

    /**
     * Mark the test as todo
     */
    public function todo(): static
    {
        $this->todo = true;

        return $this;
    }

    /**
     * Is this story marked as todo?
     */
    public function isTodo(): bool
    {
        return $this->todo !== false;
    }

    public function setUp(Closure $callback): static
    {
        $this->setUp[] = $callback;

        return $this;
    }

    public function tearDown(Closure $callback): static
    {
        $this->tearDown[] = $callback;

        return $this;
    }
}
