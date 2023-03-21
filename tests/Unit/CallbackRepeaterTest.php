<?php

use function BradieTilley\Stories\Helpers\action;

test('a callback with no repeat will only run once', function () {
    $ran = collect();

    $action = action('test action')->as(fn () => $ran[] = 'ran');
    $action->boot();

    expect($ran->toArray())->toBe([
        'ran',
    ]);
});

test('a callback with a specified repeat limit will run that many times', function () {
    $ran = collect();

    $action = action('test action')->as(fn () => $ran[] = 'ran')->repeat(4);
    $action->boot();

    expect($ran->toArray())->toBe([
        'ran',
        'ran',
        'ran',
        'ran',
    ]);
});
