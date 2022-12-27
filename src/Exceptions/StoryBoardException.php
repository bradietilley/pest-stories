<?php

namespace BradieTilley\StoryBoard\Exceptions;

use Exception;

abstract class StoryBoardException extends Exception
{
    public static function scenarioNotFound(string $scenario): ScenarioNotFoundException
    {
        return new ScenarioNotFoundException(
            sprintf('The `%s` scenario could not be found.', $scenario),
        );
    }
}