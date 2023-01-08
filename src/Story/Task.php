<?php

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use BradieTilley\StoryBoard\Exceptions\TaskGeneratorNotFoundException;
use BradieTilley\StoryBoard\Exceptions\TaskNotFoundException;
use Closure;

/**
 * @method static self make(string $name, ?Closure $generator = null, ?int $order = null)
 */
class Task extends AbstractAction
{
    public function __construct(
        protected string $name,
        protected ?Closure $generator = null,
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
     * Generator not found
     */
    protected static function generatorNotFound(string $name): TaskGeneratorNotFoundException
    {
        return StoryBoardException::taskGeneratorNotFound($name);
    }
}
