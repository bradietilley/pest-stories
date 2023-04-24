<?php

namespace BradieTilley\Stories\Exceptions;

use Exception;
use Throwable;

class FailedToIdentifyCallbackArgumentsException extends Exception
{
    public static function make(Throwable $throwable): self
    {
        return new self(
            message: sprintf(
                'Failed to identify callback arguments: %s',
                $throwable->getMessage(),
            ),
            previous: $throwable,
        );
    }
}
