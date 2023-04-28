<?php

namespace BradieTilley\Stories\Exceptions;

use Exception;

class DataVariableUnavailableException extends Exception
{
    public static function make(int|string $index): self
    {
        return new self(
            sprintf(
                'The data (variable `%s`) is unavailable',
                (string) $index,
            ),
        );
    }
}
