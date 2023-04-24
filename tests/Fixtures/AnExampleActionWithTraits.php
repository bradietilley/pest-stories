<?php

namespace Tests\Fixtures;

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Story;
use Tests\Fixtures\Traits\TestBootableTrait;

class AnExampleActionWithTraits extends Action
{
    use TestBootableTrait;

    protected string $name = 'an_example_action';

    public static array $ran = [];

    public function __invoke(Story $story): int
    {
        static::$ran[] = 'invoke';

        return 1;
    }
}
