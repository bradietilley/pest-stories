<?php

namespace Tests\Fixtures;

class NonActionExample
{
    protected string $name = 'non_action_example';

    protected string $variable = 'abc';

    public function __invoke(): void
    {
    }
}
