<?php

use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\assertion;
use function BradieTilley\Stories\Helpers\story;

test('the lifecycle of a story is as expected', function () {
    $ran = collect();

    action('action_1')
        ->before(fn () => $ran[] = 'action 1 before')
        ->after(fn () => $ran[] = 'action 1 after')
        ->as(fn () => $ran[] = 'action 1 primary');
    action('action_2')
        ->before(fn () => $ran[] = 'action 2 before')
        ->after(fn () => $ran[] = 'action 2 after')
        ->as(fn () => $ran[] = 'action 2 primary');

    assertion('assertion_1')
        ->before(fn () => $ran[] = 'assertion 1 before')
        ->after(fn () => $ran[] = 'assertion 1 after')
        ->as(fn () => $ran[] = 'assertion 1 primary');
    assertion('assertion_2')
        ->before(fn () => $ran[] = 'assertion 2 before')
        ->after(fn () => $ran[] = 'assertion 2 after')
        ->as(fn () => $ran[] = 'assertion 2 primary');

    story('story')
        ->setUp(fn () => $ran[] = 'setUp')
        ->tearDown(fn () => $ran[] = 'tearDown')
        ->before(fn () => $ran[] = 'before')
        ->after(fn () => $ran[] = 'after')
        ->as(fn () => $ran[] = 'primary')
        ->action('action_1')
        ->action('action_2')
        ->assertion('assertion_1')
        ->assertion('assertion_2')
        ->process();

    expect($ran->toArray())->toBe([
        'setUp',
        'action 1 before',
        'action 1 primary',
        'action 1 after',
        'action 2 before',
        'action 2 primary',
        'action 2 after',
        'before',
        'primary',
        'after',
        'assertion 1 before',
        'assertion 1 primary',
        'assertion 1 after',
        'assertion 2 before',
        'assertion 2 primary',
        'assertion 2 after',
        'tearDown',
    ]);
});
