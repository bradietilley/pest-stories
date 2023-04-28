<?php

use function BradieTilley\Stories\Helpers\story;
use Tests\Fixtures\AnExampleActionWithTraits;

test('traits on an action class are booted', function () {
    AnExampleActionWithTraits::$ran = [];

    story()->use();
    story()->action(AnExampleActionWithTraits::class);

    expect(AnExampleActionWithTraits::$ran)->toBe([
        'bootTestBootableTrait',
        'invoke',
    ]);
});
