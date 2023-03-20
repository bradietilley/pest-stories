<?php

use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\story;

test('a story can have one or more actions added to it', function () {
    $ran = collect();

    action('do_something_1')->as(fn () => $ran[] = 'action_1');
    action('do_something_2')->as(fn () => $ran[] = 'action_2');
    action('do_something_3')->as(fn () => $ran[] = 'action_3');

    $story = story()->as(fn () => $ran[] = 'story');

    // With no action = no actions run
    $story->process($this);
    expect($ran)->toHaveCount(1)->toArray()->toBe([
        'story',
    ]);

    // With one action = one action run
    $story->action('do_something_1');
    $story->process($this);
    expect($ran)->toHaveCount(3)->toArray()->toBe([
        // First
        'story',
        // Second
        'action_1',
        'story',
    ]);

    // With 3 actions = 3 actions run
    $story->action('do_something_3')->action('do_something_2');
    $story->process($this);
    expect($ran)->toHaveCount(7)->toArray()->toBe([
        // First
        'story',
        // Second
        'action_1',
        'story',
        // Third
        'action_1',
        'action_3',
        'action_2',
        'story',
    ]);
});

test('you can add actions to a story using various formats', function () {
    $ran = collect();

    action('by_name_1')->as(fn () => $ran[] = 'by name 1');
    action('by_name_2')->as(fn () => $ran[] = 'by name 2');
    action('by_name_3')->as(fn () => $ran[] = 'by name 3');
    $byVariable1 = action('something 1')->as(fn () => $ran[] = 'by variable 1');
    $byVariable2 = action('something 2')->as(fn () => $ran[] = 'by variable 2');
    $byVariable3 = action('something 3')->as(fn () => $ran[] = 'by variable 3');

    $story = story();

    $story->action(fn () => $ran[] = 'inline closure 1');
    $story->action('by_name_1');
    $story->action($byVariable1);

    $story->action([
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

test('the results from actions are stored in Story variables', function () {
    action('do_something')->as(fn () => 'test value here');
    $story = story('something')->action('do_something')->process();

    expect($story->get('do_something'))->toBe('test value here');
});

test('the results from actions can be stored in customised Story variables', function () {
    action('do_something')->as(fn () => 'test value here')->for('custom_name');
    $story = story('something')->action('do_something')->process();

    expect($story->get('do_something'))->toBe(null);
    expect($story->get('custom_name'))->toBe('test value here');
});
