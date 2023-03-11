<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Exceptions;

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Assertion;
use Exception;

abstract class StoryBoardException extends Exception
{
    /**
     * Exception for when a Story contains no actions (but requires at least one)
     */
    public static function runnableNotSpecified(string $runnable, Story $story): RunnableNotSpecifiedException
    {
        return new RunnableNotSpecifiedException(
            sprintf('No %s was found for the story `%s`', $runnable, $story->getFullName()),
        );
    }

    /**
     * Exception for when an `AbstractAssertion` generator callback is
     * not specified.
     */
    public static function runnableGeneratorNotFound(string $runnable, string $assertion): RunnableGeneratorNotFoundException
    {
        return new RunnableGeneratorNotFoundException(
            sprintf('The `%s` %s generator callback could not be found.', $assertion, $runnable),
        );
    }

    /**
     * Exception for when an assertion added to a story cannot not found.
     *
     * Likely causes of this is a referenced assertion contains a spelling mistake
     * or the Assertion you're referencing was never created.
     */
    public static function runnableNotFound(string $runnable, string $assertion): RunnableNotFoundException
    {
        return new RunnableNotFoundException(
            sprintf('The `%s` %s could not be found.', $assertion, $runnable),
        );
    }

    /**
     * Exception for when a Story contains no assertions (but requires at least one)
     */
    public static function assertionNotSpecified(Story $story): RunnableNotSpecifiedException
    {
        $term = $story->itCan() ? 'can' : 'cannot';

        return self::runnableNotSpecified(
            sprintf('"%s" %s', $term, Assertion::getAliasName()),
            $story,
        );
    }

    /**
     * When config `storyboard.aliases.$alias` is missing
     */
    public static function aliasNotFound(string $alias): AliasNotFoundException
    {
        return new AliasNotFoundException(
            sprintf('The `%s` alias config was not found', $alias),
        );
    }

    /**
     * When config `storyboard.aliases.$alias` is a missing class
     */
    public static function aliasClassNotFound(string $alias, string $class): AliasNotFoundException
    {
        return new AliasNotFoundException(
            sprintf('The `%s` alias class `%s` was not found', $alias, $class),
        );
    }

    /**
     * When config `storyboard.aliases.$alias` is a missing function
     */
    public static function aliasFunctionNotFound(string $alias, string $function): AliasNotFoundException
    {
        return new AliasNotFoundException(
            sprintf('The `%s` alias function `%s` was not found', $alias, $function),
        );
    }

    /**
     * When config `storyboard.aliases.$alias` is a class that is
     * not a subclass of the expected class
     */
    public static function aliasClassNotValid(string $alias, string $class, string $subclass): AliasNotFoundException
    {
        return new AliasNotFoundException(
            sprintf(
                'The `%s` alias class `%s` is not a subclass of `%s`',
                $alias,
                $class,
                $subclass,
            ),
        );
    }

    /**
     * Exception for when a Story contains no expectation (can or cannot).
     */
    public static function expectationNotSpecified(Story $story): ExpectationNotSpecifiedException
    {
        return new ExpectationNotSpecifiedException(
            sprintf('No expectation was found for the story `%s`', $story->getFullName()),
        );
    }

    /**
     * Exception for when a Trait handles a magic method (e.g. __get, __call, etc)
     * but does not "know" what to do with the given magic method call.
     */
    public static function invalidMagicMethodHandlerException(string $name, string $type): InvalidMagicMethodHandlerException
    {
        return new InvalidMagicMethodHandlerException(
            name: $name,
            type: $type,
        );
    }

    /**
     * Exception for when an invalid story object is supplied to another Story
     */
    public static function invalidStoryProvided(): InvalidStoryProvidedException
    {
        return new InvalidStoryProvidedException(
            'You must only provide Story classes to the stories() method.',
        );
    }
}
