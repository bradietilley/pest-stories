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
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use PHPUnit\Framework\TestCase;

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

    protected ?string $name = null;

    protected bool $booted = false;

    protected ?TestCase $test = null;

    protected static string $testFunction = 'test';

    public function __construct(protected ?Story $parent = null)
    {
    }

    /**
     * Create a new story
     *
     * @return $this
     */
    public static function make(?Story $parent = null): static
    {
        return new static($parent);
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
     * Boot the story scenarios and tasks
     *
     * @return $this
     */
    public function boot(): self
    {
        if ($this->skipDueToIsolation()) {
            return $this;
        }

        if ($this->booted) {
            return $this;
        }

        $this->bootScenarios();
        $this->bootTask();
        $this->booted = true;

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
     * Get the group key to use to isolate this class.
     */
    protected static function getIsolationClassGroup(): string
    {
        return 'story';
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
