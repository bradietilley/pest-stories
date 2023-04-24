<?php

namespace Tests\Fixtures;

use BradieTilley\Stories\Contracts\Deferred;

class DeferredAction extends DeferrableAction implements Deferred
{
    protected string $name = 'deferred_action';
}
