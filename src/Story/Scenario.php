<?php

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\ScenarioNotFoundException;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use Closure;

class Scenario extends AbstractAction
{
    protected string $variable;

    public function __construct(
        protected string $name,
        protected Closure $generator,
        ?string $variable = null,
        ?int $order = null,
    ) {
        $this->variable = $variable ?? $name;

        parent::__construct($name, $generator, $order ?? self::getNextOrder());
    }

    /**
     * Scenario not found
     */
    protected static function notFound(string $name): ScenarioNotFoundException
    {
        return StoryBoardException::scenarioNotFound($name);
    }

    /**
     * Get the name of the variable
     */
    public function variable(): string
    {
        return $this->variable;
    }
}
