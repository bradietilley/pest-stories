<?php

namespace Tests\Fixtures;

use BradieTilley\Stories\Action;

class AnExampleActionWithManyArguments extends Action
{
    public function __invoke(
        int $abc,
        string $def,
        NonActionExample $ghi,
        AnExampleAction $jkl,
    ) {
        return $abc;
    }
}
