<?php

use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\story;
use Illuminate\Support\Collection;

test('an action can be repeated several times', function () {
    $ran = Collection::make();

    action('test_something', fn () => $ran[] = 'run')->repeat(5)->run(story());

    expect($ran->toArray())->toBe([
        'run',
        'run',
        'run',
        'run',
        'run',
    ]);
});

test('an action is run once by default', function () {
    $ran = Collection::make();

    action('test_something', fn () => $ran[] = 'run')->run(story());

    expect($ran->toArray())->toBe([
        'run',
    ]);
});
