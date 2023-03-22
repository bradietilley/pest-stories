<?php

use function BradieTilley\Stories\Helpers\assertion;
use function BradieTilley\Stories\Helpers\story;

test('a story can have one or more assertions added to it', function () {
    $ran = collect();

    assertion('do_something_1')->as(fn () => $ran[] = 'assertion_1');
    assertion('do_something_2')->as(fn () => $ran[] = 'assertion_2');
    assertion('do_something_3')->as(fn () => $ran[] = 'assertion_3');

    $story = story()->as(fn () => $ran[] = 'story');

    // With no assertion = no assertions run
    $story->process();
    expect($ran)->toHaveCount(1)->toArray()->toBe([
        'story',
    ]);

    // With one assertion = one assertion run
    $story->assertion('do_something_1');
    $story->process();
    expect($ran)->toHaveCount(3)->toArray()->toBe([
        // First
        'story',
        // Second
        'story',
        'assertion_1',
    ]);

    // With 3 assertions = 3 assertions run
    $story->assertion('do_something_3')->assertion('do_something_2');
    $story->process();
    expect($ran)->toHaveCount(7)->toArray()->toBe([
        // First
        'story',
        // Second
        'story',
        'assertion_1',
        // Third
        'story',
        'assertion_1',
        'assertion_3',
        'assertion_2',
    ]);
});

test('you can add assertions to a story using various formats', function () {
    $ran = collect();

    assertion('by_name_1')->as(fn () => $ran[] = 'by name 1');
    assertion('by_name_2')->as(fn () => $ran[] = 'by name 2');
    assertion('by_name_3')->as(fn () => $ran[] = 'by name 3');
    $byVariable1 = assertion('something 1')->as(fn () => $ran[] = 'by variable 1');
    $byVariable2 = assertion('something 2')->as(fn () => $ran[] = 'by variable 2');
    $byVariable3 = assertion('something 3')->as(fn () => $ran[] = 'by variable 3');

    $story = story();

    $story->assertion(fn () => $ran[] = 'inline closure 1');
    $story->assertion('by_name_1');
    $story->assertion($byVariable1);

    $story->assertion([
        fn () => $ran[] = 'inline closure 2',
        fn () => $ran[] = 'inline closure 3',
        'by_name_2',
        'by_name_3',
        $byVariable2,
        $byVariable3,
    ]);

    $story->process();
    expect($ran)->toHaveCount(9)->toArray()->toBe([
        'inline closure 1',
        'by name 1',
        'by variable 1',
        'inline closure 2',
        'inline closure 3',
        'by name 2',
        'by name 3',
        'by variable 2',
        'by variable 3',
    ]);
});
