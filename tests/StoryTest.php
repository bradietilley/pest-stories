<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;

test('a storyboard with a single story can generate test cases with names', function () {
    $storyboard = StoryBoard::make()->name('Can create something cool');
    $tests = $storyboard->allStories();

    expect($tests)->toHaveCount(1)->toHaveKey('Can create something cool');
    expect($tests['Can create something cool'])->toBeInstanceOf(Story::class);
});

test('a storyboard with multiple stories can generate test cases with names', function () {
    $storyboard = StoryBoard::make()
        ->name('create something cool')
        ->stories([
            Story::make()->name('as admin')->scenario('as_admin')->can(),
            Story::make()->name('as customer')->scenario('as_customer')->cannot(),
        ]);

    $tests = $storyboard->allStories();

    $expectedKeys = [
        '[Can] create something cool as admin',
        '[Cannot] create something cool as customer',
    ];

    expect($tests)->toHaveCount(count($expectedKeys));
    expect($tests)->toHaveKeys($expectedKeys);
});

test('a storyboard with multiple nested stories can generate test cases with names', function () {
    $storyboard = StoryBoard::make()
        ->name('create something cool')
        ->stories([
            Story::make()->name('as admin')->scenario('as_admin')->stories([
                Story::make()->name('if not blocked')->scenario('as_unblocked')->can(),
                Story::make()->name('if blocked')->scenario('as_blocked')->cannot(),
            ]),
            Story::make()->name('as customer')->scenario('as_customer')->stories([
                Story::make()->name('if not blocked')->scenario('as_unblocked')->cannot(),
                Story::make()->name('if blocked')->scenario('as_blocked')->cannot(),
            ]),
        ]);

    $tests = $storyboard->allStories();

    $expectedKeys = [
        '[Can] create something cool as admin if not blocked',
        '[Cannot] create something cool as admin if blocked',
        '[Cannot] create something cool as customer if not blocked',
        '[Cannot] create something cool as customer if blocked',
    ];

    expect($tests)->toHaveCount(count($expectedKeys));
    expect($tests)->toHaveKeys($expectedKeys);
});

test('a storyboard with multiple nested stories can collate required scenarios', function () {
    $storyboard = StoryBoard::make()
        ->name('create something cool')
        ->scenario('allows_creation')
        ->stories([
            Story::make()->name('as admin')->scenario('as_admin')->stories([
                Story::make()->name('if not blocked')->scenario('as_unblocked')->can(),
                Story::make()->name('if blocked')->scenario('as_blocked')->cannot(),
            ]),
            Story::make()->name('as customer')->scenario('as_customer')->stories([
                Story::make()->name('if not blocked')->scenario('as_unblocked')->cannot(),
                Story::make()->name('if blocked')->scenario('as_blocked')->cannot(),
            ]),
        ]);

    $tests = $storyboard->allStories();

    $expect = [
        '[Can] create something cool as admin if not blocked' => [
            'allows_creation',
            'as_admin',
            'as_unblocked',
        ],
        '[Cannot] create something cool as admin if blocked' => [
            'allows_creation',
            'as_admin',
            'as_blocked',
        ],
        '[Cannot] create something cool as customer if not blocked' => [
            'allows_creation',
            'as_customer',
            'as_unblocked',
        ],
        '[Cannot] create something cool as customer if blocked' => [
            'allows_creation',
            'as_customer',
            'as_blocked',
        ],
    ];
    $actual = [];

    foreach ($tests as $key => $story) {
        $scenarios = array_keys($story->allScenarios());

        $actual[$key] = $scenarios;
    }

    expect($actual)->toBe($expect);
});
