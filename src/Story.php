<?php

namespace BradieTilley\StoryBoard;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Exceptions\TestFunctionNotFoundException;
use BradieTilley\StoryBoard\Traits\HasContainer;
use BradieTilley\StoryBoard\Traits\HasData;
use BradieTilley\StoryBoard\Traits\HasInheritance;
use BradieTilley\StoryBoard\Traits\HasIsolation;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasNameShortcuts;
use BradieTilley\StoryBoard\Traits\HasPerformer;
use BradieTilley\StoryBoard\Traits\HasScenarios;
use BradieTilley\StoryBoard\Traits\HasStories;
use BradieTilley\StoryBoard\Traits\HasTasks;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use PHPUnit\Framework\TestCase;

/**
 * @property-read Collection<int,Story> $storiesDirect
 * @property-read Collection<string,Story> $storiesAll
 */
class Story
{
    use HasData;
    use HasName;
    use HasNameShortcuts;
    use HasPerformer;
    use HasScenarios;
    use HasStories;
    use HasTasks;
    use HasInheritance;
    use HasContainer;
    use HasIsolation;
    use Conditionable;
    use Macroable;

    protected bool $registered = false;

    protected bool $booted = false;

    protected ?TestCase $test = null;

    protected static string $testFunction = 'test';

    public function __construct(protected ?string $name = null, protected ?Story $parent = null)
    {
    }

    /**
     * Proxy certain property getters to methods
     * 
     * @param string $name
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

        return $this->{$name};
    }

    /**
     * Create a new story
     *
     * @return $this
     */
    public static function make(?string $name = null, ?Story $parent = null): static
    {
        return new static($name, $parent);
    }

    /**
     * Get parameters available for DI callbacks
     *
     * @return array
     */
    public function getParameters(): array
    {
        return array_replace($this->allData(), [
            'story' => $this,
            'test' => $this->getTest(),
            'can' => $this->canAssertion,
            'user' => $this->getUser(),
            'result' => $this->getResult(),
        ]);
    }

    /**
     * Register this story scenarios and tasks
     * 
     * @return $this 
     */
    public function register(): self
    {
        if ($this->skipDueToIsolation()) {
            return $this;
        }

        if ($this->registered) {
            return $this;
        }

        $this->registered = true;
        $this->registerScenarios();
        $this->registerTask();

        return $this;
    }

    /**
     * Boot the story scenarios and tasks
     *
     * @return $this
     */
    public function boot(): self
    {
        $this->register();

        if ($this->skipDueToIsolation()) {
            return $this;
        }

        if ($this->booted) {
            return $this;
        }
        
        $this->booted = true;
        $this->bootScenarios();
        $this->bootTask();

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
    public function setTest(TestCase $test): self
    {
        $this->test = $test;

        return $this;
    }

    /**
     * Create a test case for this story (e.g. create a `test('name', fn () => ...)`)
     *
     * @return $this
     */
    public function test(): self
    {
        $story = $this;

        $function = self::getTestFunction();
        $args = [
            $this->getFullName(),
            function () use ($story) {
                /** @var Story $story */
                /** @var TestCase $this */
    
                $story->setTest($this)->boot()->assert();
            },
        ];

        /**
         * Pest uses a Backtrace class which expects the most recent backtrace items
         * to each include a file. By running call_user_func we lose the 'file' in the
         * relevant backtrace and therefore Pest cannot operate. So instead we'll call
         * the function directly. Not super nice, but hey.
         */
        $function(...$args);

        return $this;
    }

    /**
     * Set the name of the function that powers the testing. Default: `test`

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
}
