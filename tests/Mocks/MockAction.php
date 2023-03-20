<?php

namespace Tests\Mocks;

use BradieTilley\Stories\Action;

class MockAction extends Action
{
    public function boot(array $arguments = []): mixed
    {
        return null;
    }
}
