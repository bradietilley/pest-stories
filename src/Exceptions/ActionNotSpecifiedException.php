<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Exceptions;

/**
 * Exception for when a Story contains no actions (but requires at least one)
 */
class ActionNotSpecifiedException extends StoryBoardException
{
}
