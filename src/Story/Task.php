<?php

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\TaskNotFoundException;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use Closure;

class Task extends AbstractAction
{
    public function __construct(
        protected string $name,
        protected Closure $generator,
        ?int $order = null,
    ) {
        parent::__construct($name, $generator, $order ?? self::getNextOrder());
    }

    /**
     * Task not found
     */
    protected static function notFound(string $name): TaskNotFoundException
    {
        return StoryBoardException::taskNotFound($name);
    }

    /**
     * Get the name of the variable
     */
    public function variable(): string
    {
        return $this->variable;
    }
}
