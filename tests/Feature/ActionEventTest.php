<?php

use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Story;
use Illuminate\Support\Collection;

test('an action before and after event correctly fire in order', function () {
    Story::setInstance(story());
    $ran = Collection::make();

    action('a1')
        ->as(fn () => $ran[] = 'a1:as')
        ->before(fn () => $ran[] = 'a1:before')
        ->after(fn () => $ran[] = 'a1:after');

    action('a2')
        ->as(fn () => $ran[] = 'a2:as')
        ->before(fn () => $ran[] = 'a2:before')
        ->after(fn () => $ran[] = 'a2:after');

    action('a3')
        ->as(fn () => $ran[] = 'a3:as')
        ->before(fn () => $ran[] = 'a3:before')
        ->after(fn () => $ran[] = 'a3:after');

    action('b')
        ->before(fn () => $ran[] = 'b:before')
        ->as(fn () => $ran[] = 'b:as')
        ->after(fn () => $ran[] = 'b:after')
        ->action('a1')
        ->action('a2')
        ->action('a3');

    story()->action('b');

    expect($ran->toArray())->toBe([
        'b:before',
        'a1:before',
        'a1:as',
        'a1:after',
        'a2:before',
        'a2:as',
        'a2:after',
        'a3:before',
        'a3:as',
        'a3:after',
        'b:as',
        'b:after',
    ]);
});
