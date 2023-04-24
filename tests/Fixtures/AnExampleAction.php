<?php

namespace Tests\Fixtures;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Story;

class AnExampleAction extends Action
{
    protected string $name = 'an_example_action';

    protected string $variable = 'abc';

    public static array $ran = [];

    public function __invoke(Story $story, int $abc): int
    {
        static::$ran[] = 'abc:'.$abc;

        return $abc * 2;
    }
}
