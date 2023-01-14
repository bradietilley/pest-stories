<?php

namespace BradieTilley\StoryBoard\Exceptions;

use BradieTilley\StoryBoard\Story;
use Exception;

abstract class StoryBoardException extends Exception
{
    public static function actionNotFound(string $action): ActionNotFoundException
    {
        return new ActionNotFoundException(
            sprintf('The `%s` action could not be found.', $action),
        );
    }

    public static function actionGeneratorNotFound(string $action): ActionGeneratorNotFoundException
    {
        return new ActionGeneratorNotFoundException(
            sprintf('The `%s` action generator callback could not be found.', $action),
        );
    }

    public static function actionNotSpecified(Story $story): ActionNotSpecifiedException
    {
        return new ActionNotSpecifiedException(
            sprintf('No action was found for the story `%s`', $story->getFullName()),
        );
    }

    public static function invalidStory(): InvalidStoryException
    {
        return new InvalidStoryException(
            'You must only provide Story classes to the stories() method.',
        );
    }

    public static function assertionNotFound(Story $story): AssertionNotFoundException
    {
        return new AssertionNotFoundException(
            sprintf('No assertion was found for the story `%s`', $story->getFullName()),
        );
    }

    public static function assertionCheckerNotFound(Story $story): AssertionCheckerNotFoundException
    {
        $term = $story->itCan() ? 'can' : 'cannot';

        return new AssertionCheckerNotFoundException(
            sprintf('No "%s" assertion checker was found for the story `%s`', $term, $story->getFullName()),
        );
    }

    /**
     * Internally used for when a Trait's magic method handler doesn't know what to do with a
     * given property or method call.
     */
    public static function invalidMagicAliasException(string $name, string $type): InvalidMagicAliasException
    {
        return new InvalidMagicAliasException(
            name: $name,
            type: $type,
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
}
