<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Exceptions;

use BradieTilley\Stories\Action;
use Exception;

class ActionMustAcceptAllDatasetArgumentsException extends Exception
{
    public static function make(Action $action, int $datasetIndexMissing): self
    {
        return new self(
            sprintf(
                'The `%s` action is missing dataset argument #%d',
                $action->getName(),
                $datasetIndexMissing,
            ),
        );
    }
}
