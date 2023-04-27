<?php

namespace BradieTilley\Stories\Exceptions;

use Exception;

class DatasetVariableUnavailableException extends Exception
{
    public static function make(int $index): self
    {
        return new self(
            sprintf(
                'The dataset (variable #%d) is unavailable',
                $index,
            ),
        );
    }
}
