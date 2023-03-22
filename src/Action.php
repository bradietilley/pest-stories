<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use Illuminate\Support\Traits\Macroable;

class Action extends Callback
{
    use Macroable;

    /**
     * Get the key used to find the aliased class
     */
    public static function getAliasKey(): string
    {
        return 'action';
    }
}
