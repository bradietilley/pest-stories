<?php

namespace Tests\Mocks;

use BradieTilley\Stories\Assertion;

class MockAssertion extends Assertion
{
    public function boot(array $arguments = []): mixed
    {
        return null;
    }
}
