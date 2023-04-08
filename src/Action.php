<?php

declare(strict_types=1);

namespace BradieTilley\Stories;

use BradieTilley\Stories\Traits\HasSequences;
use Illuminate\Support\Traits\Macroable;

class Action extends Callback
{
    use Macroable;
    use HasSequences;

    /**
     * Get the key used to find the aliased class
     */
    public static function getAliasKey(): string
    {
        return 'action';
    }
}
