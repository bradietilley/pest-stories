<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Exceptions;

/**
 * Exception for when an assertion added to a story cannot not found.
 *
 * Likely causes of this is a referenced assertion contains a spelling mistake
 * or the Assertion you're referencing was never created.
 */
class AssertionNotFoundException extends StoryBoardException
{
}
