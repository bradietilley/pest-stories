<?php

namespace Tests\Fixtures;

use BradieTilley\Stories\Action;

class AnExampleActionWithData extends Action
{
    public function __invoke(): void
    {
        $this->set('abc', 123);
    }
}
