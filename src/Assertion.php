<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

class Assertion extends Callback
{
    /**
     * Get the key used to find the aliased class
     */
    public static function getAliasKey(): string
    {
        return 'assertion';
    }
}
