<?php

use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\assertion;
use function BradieTilley\Stories\Helpers\repeater;
use BradieTilley\Stories\Sequence;
use Illuminate\Support\Collection;

test('an action can be created with a sequence of callbacks', function () {
    $ran = Collection::make([]);

    action('a')->as(fn () => $ran[] = 'a');
    action('b')->as(fn () => $ran[] = 'b');
    action('c')->as(fn () => $ran[] = 'c');
    assertion('1')->as(fn () => $ran[] = '1');
    assertion('2')->as(fn () => $ran[] = '2');
    assertion('3')->as(fn () => $ran[] = '3');

    $test = action('test_action')->sequence([
        action()->fetch('a'),
        assertion()->fetch('1'),
        action()->fetch('b'),
        assertion()->fetch('2'),
        action()->fetch('c'),
        assertion()->fetch('3'),
    ]);

    $test->boot();

    expect($ran->toArray())->toBe([
        'a',
        '1',
        'b',
        '2',
        'c',
        '3',
    ]);
});

test('an action cannot be created with a sequence of non-callbacks', function () {
    action('a')->as(fn () => $ran[] = 'a');

    action('test_action')->sequence([
        action()->fetch('a'),
        repeater(1),
    ]);
})->throws('Unsupported callback in Sequence');

test('an action may have a sequence specified with chained action and assertion methods', function () {
    $ran = Collection::make([]);

    action('a')->as(fn () => $ran[] = 'a');
    action('b')->as(fn () => $ran[] = 'b');
    action('c')->as(fn () => $ran[] = 'c');
    assertion('1')->as(fn () => $ran[] = '1');
    assertion('2')->as(fn () => $ran[] = '2');
    assertion('3')->as(fn () => $ran[] = '3');

    $test = action('test_action')->sequence(fn (Sequence $sequence) => $sequence->action('a')
            ->assertion('1')
            ->action('b')
            ->assertion('2')
            ->action('c')
            ->assertion('3')
    );

    $test->boot();

    expect($ran->toArray())->toBe([
        'a',
        '1',
        'b',
        '2',
        'c',
        '3',
    ]);
});
