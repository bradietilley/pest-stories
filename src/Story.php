<?php

namespace BradieTilley\StoryBoard;

use BradieTilley\StoryBoard\Traits\HasData;
use BradieTilley\StoryBoard\Traits\HasInheritance;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasNameShortcuts;
use BradieTilley\StoryBoard\Traits\HasPerformer;
use BradieTilley\StoryBoard\Traits\HasScenarios;
use BradieTilley\StoryBoard\Traits\HasStories;
use BradieTilley\StoryBoard\Traits\HasTask;
use Illuminate\Support\Traits\Conditionable;

class Story
{
    use HasData;
    use HasName;
    use HasNameShortcuts;
    use HasPerformer;
    use HasScenarios;
    use HasStories;
    use HasTask;
    use HasInheritance;
    use Conditionable;

    protected ?string $name = null;

    protected bool $booted = false;

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
            'can' => $this->checkCan,
            'user' => $this->getUser(),
        ]);
    }

    /**
     * Boot the story scenarios and tasks
     * 
     * @return $this
     */
    public function boot(): self
    {
        if ($this->booted) {
            return $this;
        } 

        $this->bootScenarios();
        $this->bootTask();
        $this->booted = true;

        return $this;
    }

    /**
     * Create a test case for this story (e.g. create a `test('name', fn () => ...)`)
     * 
     * @return $this
     */
    public function createTestCase(): self
    {
        $story = $this;

        test($this->getFullName(), function () use ($story) {
            $story->boot()->assert();
        });

        return $this;
    }
}