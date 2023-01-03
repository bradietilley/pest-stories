<?php

namespace BradieTilley\StoryBoard;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Exceptions\TestFunctionNotFoundException;
use BradieTilley\StoryBoard\Traits\HasCallbacks;
use BradieTilley\StoryBoard\Traits\HasData;
use BradieTilley\StoryBoard\Traits\HasInheritance;
use BradieTilley\StoryBoard\Traits\HasIsolation;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasNameShortcuts;
use BradieTilley\StoryBoard\Traits\HasPerformer;
use BradieTilley\StoryBoard\Traits\HasScenarios;
use BradieTilley\StoryBoard\Traits\HasStories;
use BradieTilley\StoryBoard\Traits\HasTasks;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use PHPUnit\Framework\TestCase;

/**
 * @property-read Collection<int,Story> $storiesDirect
 * @property-read Collection<string,Story> $storiesAll
 * @property-read ?Authenticatable $user
 */
class Story
{
    use Conditionable;
    use HasCallbacks;
    use HasData;
    use HasName;
    use HasNameShortcuts;
    use HasInheritance;
    use HasIsolation;
    use HasPerformer;
    use HasScenarios;
    use HasStories;
    use HasTasks;
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

        if ($name === 'user') {
            return $this->getUser();
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
    public function getParameters(array $additional = []): array
    {
        return array_replace($this->allData(), [
            'story' => $this,
            'test' => $this->getTest(),
            'can' => $this->can,
            'user' => $this->getUser(),
            'result' => $this->getResult(),
        ], $additional);
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
        $this->inherit();
        $this->registerScenarios();
        $this->registerTasks();

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
        $this->bootTasks();

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

    public function inherit(): self
    {
        $this->inheritName();
        $this->inheritData();
        $this->inheritScenarios();
        $this->inheritTasks();
        $this->inheritCallbacks();

        return $this;
    }

    /**
     * Register a callback to run when the test is set up
     */
    public function setUp(?Closure $callback): self
    {
        return $this->setCallback('setUp', $callback);
    }

    /**
     * Register a callback to run when the test the teared down
     */
    public function tearDown(?Closure $callback): self{
        return $this->setCallback('tearDown', $callback);
    }
}
