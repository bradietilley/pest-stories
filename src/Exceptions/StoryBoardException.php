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

    public static function testFunctionNotFound(string $function): TestFunctionNotFoundException
    {
        return new TestFunctionNotFoundException(
            sprintf('The story test function `%s` could not be found', $function),
        );
    }
}
