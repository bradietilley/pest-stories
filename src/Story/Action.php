<?php

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\ActionGeneratorNotFoundException;
use BradieTilley\StoryBoard\Exceptions\ActionNotFoundException;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use Closure;

/**
 * @method static self make(string $name, ?Closure $generator = null, ?string $variable = null, ?int $order = null)
 */
class Action extends AbstractAction
{
    protected string $variable;

    protected ?string $appendName = null;

    public function __construct(
        protected string $name,
        protected ?Closure $generator = null,
        ?string $variable = null,
        ?int $order = null,
    ) {
        $this->variable = $variable ?? $name;

        parent::__construct($name, $generator, $order ?? self::getNextOrder());
    }

    /**
     * Action not found
     */
    protected static function notFound(string $name): ActionNotFoundException
    {
        return StoryBoardException::actionNotFound($name);
    }

    /**
     * Generator not found
     */
    protected static function generatorNotFound(string $name): ActionGeneratorNotFoundException
    {
        return StoryBoardException::actionGeneratorNotFound($name);
    }

    /**
     * Get the name of the variable
     */
    public function getVariable(): string
    {
        return $this->variable;
    }

    /**
     * Set the name of the variable
     *
     * @return $this;
     */
    public function variable(string $variable): self
    {
        $this->variable = $variable;

        return $this;
    }

    /**
     * Append the name of this action to the tests
     *
     * @return $this
     */
    public function appendName(?string $name = null): self
    {
        $this->appendName = $name ?? str_replace('_', ' ', $this->getName());

        return $this;
    }

    /**
     * Get the name to append to the test case, if any was specified
     */
    public function getAppendName(): ?string
    {
        return $this->appendName;
    }
}
