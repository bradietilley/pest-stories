<?php

namespace BradieTilley\StoryBoard\Exceptions;

/**
 * Exception for when an
 *
 * - alias class or function was not specified
 * - alias class or function does not exist
 * - alias class is not a subclass of the expected class type.
 */
class AliasNotFoundException extends StoryBoardException
{
}
