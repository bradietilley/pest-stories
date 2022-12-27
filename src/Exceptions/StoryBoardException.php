<?php

namespace BradieTilley\StoryBoard\Exceptions;

use BradieTilley\StoryBoard\Story;
use Exception;

abstract class StoryBoardException extends Exception
{
    public static function scenarioNotFound(string $scenario): ScenarioNotFoundException
    {
        return new ScenarioNotFoundException(
            sprintf('The `%s` scenario could not be found.', $scenario),
        );
    }

    public static function invalidStory(): InvalidStoryException
    {
        return new InvalidStoryException(
            'You must only provide Story classes to the stories() method.',
        );
    }

    public static function taskNotFound(Story $story): TaskNotFoundException
    {
        return new TaskNotFoundException(
            sprintf('No task was found for the story `%s`', $story->getFullName()),
        );
    }

    public static function assertionNotFound(Story $story): AssertionNotFoundException
    {
        return new AssertionNotFoundException(
            sprintf('No assertion was found for the story `%s`', $story->getFullName()),
        );
    }

    public static function assertionCheckerNotFound(Story $story): AssertionNotFoundException
    {
        $term = $story->getCan() ? 'can' : 'cannot';

        return new AssertionNotFoundException(
            sprintf('No "%s" assertion checker was found for the story `%s`', $term, $story->getFullName()),
        );
    }
}