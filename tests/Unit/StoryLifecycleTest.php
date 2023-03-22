<?php

use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\assertion;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Helpers\StoryAliases;

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

test('the lifecycle of a story is as expected with inheritance', function () {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');
    $ran = collect();

    action('action_1')
        ->before(fn () => $ran[] = 'action 1 before')
        ->after(fn () => $ran[] = 'action 1 after')
        ->as(fn () => $ran[] = 'action 1 primary');
    action('action_2')
        ->before(fn () => $ran[] = 'action 2 before')
        ->after(fn () => $ran[] = 'action 2 after')
        ->as(fn () => $ran[] = 'action 2 primary');
    action('action_3')
        ->before(fn () => $ran[] = 'action 3 before')
        ->after(fn () => $ran[] = 'action 3 after')
        ->as(fn () => $ran[] = 'action 3 primary');
    action('action_4')
        ->before(fn () => $ran[] = 'action 4 before')
        ->after(fn () => $ran[] = 'action 4 after')
        ->as(fn () => $ran[] = 'action 4 primary');

    assertion('assertion_1')
        ->before(fn () => $ran[] = 'assertion 1 before')
        ->after(fn () => $ran[] = 'assertion 1 after')
        ->as(fn () => $ran[] = 'assertion 1 primary');
    assertion('assertion_2')
        ->before(fn () => $ran[] = 'assertion 2 before')
        ->after(fn () => $ran[] = 'assertion 2 after')
        ->as(fn () => $ran[] = 'assertion 2 primary');
    assertion('assertion_3')
        ->before(fn () => $ran[] = 'assertion 3 before')
        ->after(fn () => $ran[] = 'assertion 3 after')
        ->as(fn () => $ran[] = 'assertion 3 primary');
    assertion('assertion_4')
        ->before(fn () => $ran[] = 'assertion 4 before')
        ->after(fn () => $ran[] = 'assertion 4 after')
        ->as(fn () => $ran[] = 'assertion 4 primary');

    story('story')
        ->setUp(fn () => $ran[] = 'setUp-1')
        ->tearDown(fn () => $ran[] = 'tearDown-1')
        ->before(fn () => $ran[] = 'before-1')
        ->after(fn () => $ran[] = 'after-1')
        ->as(fn () => $ran[] = 'primary-1')
        ->action('action_1')
        ->action('action_2')
        ->assertion('assertion_1')
        ->assertion('assertion_2')
        ->stories([
            story()
                ->setUp(fn () => $ran[] = 'setUp-2')
                ->tearDown(fn () => $ran[] = 'tearDown-2')
                ->before(fn () => $ran[] = 'before-2')
                ->after(fn () => $ran[] = 'after-2')
                ->as(fn () => $ran[] = 'primary-2')
                ->action('action_3')
                ->assertion('assertion_3')
                ->stories([
                    story()
                        ->setUp(fn () => $ran[] = 'setUp-3')
                        ->tearDown(fn () => $ran[] = 'tearDown-3')
                        ->before(fn () => $ran[] = 'before-3')
                        ->after(fn () => $ran[] = 'after-3')
                        ->as(fn () => $ran[] = 'primary-3')
                        ->action('action_4')
                        ->assertion('assertion_4'),
                ]),
        ])
        ->test()
        ->run();

    expect($ran->toArray())->toBe([
        /**
         * The set up callbacks inherit and run in order
         */
        'setUp-1',
        'setUp-2',
        'setUp-3',
        /**
         * The actions inherit and run in order
         */
        'action 1 before',
        'action 1 primary',
        'action 1 after',
        'action 2 before',
        'action 2 primary',
        'action 2 after',
        'action 3 before',
        'action 3 primary',
        'action 3 after',
        'action 4 before',
        'action 4 primary',
        'action 4 after',
        /**
         * The before callbacks inherit and run in order
         */
        'before-1',
        'before-2',
        'before-3',
        /**
         * The primary callback doesn't inherit
         */
        'primary-3',
        /**
         * The after callbacks inherit and run in order
         */
        'after-1',
        'after-2',
        'after-3',
        /**
         * The assertions inherit and run in order
         */
        'assertion 1 before',
        'assertion 1 primary',
        'assertion 1 after',
        'assertion 2 before',
        'assertion 2 primary',
        'assertion 2 after',
        'assertion 3 before',
        'assertion 3 primary',
        'assertion 3 after',
        'assertion 4 before',
        'assertion 4 primary',
        'assertion 4 after',
        /**
         * The tear down callbacks inherit and run in order
         */
        'tearDown-1',
        'tearDown-2',
        'tearDown-3',
    ]);
});
