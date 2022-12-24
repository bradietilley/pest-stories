<?php

namespace BradieTilley\StoryBoard;

use BradieTilley\StoryBoard\Traits\HasData;
use BradieTilley\StoryBoard\Traits\HasName;
use BradieTilley\StoryBoard\Traits\HasNameShortcuts;
use BradieTilley\StoryBoard\Traits\HasPerformer;
use BradieTilley\StoryBoard\Traits\HasScenarios;
use BradieTilley\StoryBoard\Traits\HasStories;
use BradieTilley\StoryBoard\Traits\HasTask;

class Story
{
    use HasData;
    use HasName;
    use HasNameShortcuts;
    use HasPerformer;
    use HasScenarios;
    use HasStories;
    use HasTask;

    protected ?string $name = null;

    public function __construct(protected ?Story $parent = null)
    {
    }

    /**
     * @return $this
     */
    public static function make(?Story $parent = null)
    {
        return new self($parent);
    }

    public function hasParent(): bool
    {
        return $this->parent !== null;
    }

    /**
     * Get the parent Story
     */
    public function getParent(): ?Story
    {
        return $this->parent;
    }

    /**
     * Set the parent of this story
     * 
     * @return $this 
     */
    public function setParent(Story $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function run(): self
    {
        $this->bootScenarios();

        return $this;
    }
}