<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Story;

use BradieTilley\StoryBoard\Exceptions\AssertionGeneratorNotFoundException;
use BradieTilley\StoryBoard\Exceptions\AssertionNotFoundException;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;
use Closure;

/**
 * @method static self make(string $name, ?Closure $generator = null, ?string $variable = null, ?int $order = null)
 */
class Assertion extends AbstractAction
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
     * Get the alias for this type of assertion (for use in config)
     */
    public static function getAliasName(): string
    {
        return 'assertion';
    }

    /**
     * Assertion not found
     */
    protected static function notFound(string $name): AssertionNotFoundException
    {
        return StoryBoardException::assertionNotFound($name);
    }

    /**
     * Generator not found
     */
    protected static function generatorNotFound(string $name): AssertionGeneratorNotFoundException
    {
        return StoryBoardException::assertionGeneratorNotFound($name);
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
     */
    public function variable(string $variable): static
    {
        $this->variable = $variable;

        return $this;
    }

    /**
     * Append the name of this assertion to the tests
     */
    public function appendName(string $name = null): static
    {
        $this->appendName = $name ?? str_replace('_', ' ', $this->getNameString());

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
