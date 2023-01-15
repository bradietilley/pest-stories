<?php

namespace BradieTilley\StoryBoard\Exceptions;

/**
 * Exception for when a Story contains no assertions (but requires one
 * for the given expectation of can or cannot).
 */
class AssertionCheckerNotSpecifiedException extends StoryBoardException
{
}