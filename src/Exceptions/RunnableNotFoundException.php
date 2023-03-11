<?php

declare(strict_types=1);

namespace BradieTilley\StoryBoard\Exceptions;

/**
 * Exception for when a runnable referenced by a story cannot not found.
 *
 * Likely causes of this is a referenced runnable contains a spelling mistake
 * or the runnable you're referencing was never created.
 */
class RunnableNotFoundException extends StoryBoardException
{
}
