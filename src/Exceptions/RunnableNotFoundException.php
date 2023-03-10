<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Exceptions;

/**
 * Exception for when an action added to a story cannot not found.
 *
 * Likely causes of this is a referenced action contains a spelling mistake
 * or the Action you're referencing was never created.
 */
class RunnableNotFoundException extends StoryBoardException
{
}
