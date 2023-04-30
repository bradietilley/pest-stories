<?php

declare(strict_types=1);

namespace BradieTilley\Stories\Exceptions;

use Exception;

class ProxyDataUnknownClassTypeException extends Exception
{
    public static function make(object $class): self
    {
        return new self(
            sprintf(
                'Unknown class thpe for ProxiesData class `%s`',
                get_class($class) ?: 'unknown',
            ),
        );
    }
}
