<?php

use function BradieTilley\Stories\Helpers\action;

test('can fetch an action', function () {
    $actionExpect = action('some_action_that_we_can_fetch', fn () => null);
    $actionActual = action()->fetch('some_action_that_we_can_fetch');
    expect($actionActual)->toBe($actionExpect);

    $actionNewExpect = action('some_action_that_we_can_fetch', fn () => null);
    $actionNewActual = action()->fetch('some_action_that_we_can_fetch');
    expect($actionNewActual)->toBe($actionNewExpect);
});
