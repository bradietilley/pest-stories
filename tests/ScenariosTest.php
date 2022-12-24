<?php

use BradieTilley\StoryBoard\Scenario;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Contracts\Container\Container;

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
        $scenarios = array_keys($story->getScenarios());

        $actual[$key] = $scenarios;
    }

    expect($actual)->toBe($expect);
});

test('scenario callbacks are executed when a story boots its scenarios', function () {
    $test = [
        'creation' => [],
        'role' => [],
        'blocked' => [],
        'variable' => [],
    ];

    Scenario::make('allows_creation', 'creation', function () use (&$test) {
        $test['creation'][] = true;
    });
    Scenario::make('as_admin', 'role', function () use (&$test) {
        $test['role'][] = 'admin';
    });
    Scenario::make('as_customer', 'role', function () use (&$test) {
        $test['role'][] = 'customer';
    });
    Scenario::make('as_blocked', 'blocked', function () use (&$test) {
        $test['blocked'][] = true;
    });
    Scenario::make('as_unblocked', 'blocked', function () use (&$test) {
        $test['blocked'][] = false;
    });
    Scenario::make('with_variable', 'var', function (string $name) use (&$test) {
        $test['variable'][] = $name;
    });

    $story = Story::make()
        ->scenario('allows_creation')
        ->scenario('as_admin')
        ->scenario('as_blocked')
        ->scenario('with_variable', [
            'name' => 'Something cool',
        ]);

    expect($story->getScenarios())->toBe([
        'allows_creation' => [],
        'as_admin' => [],
        'as_blocked' => [],
        'with_variable' => [
            'name' => 'Something cool',
        ],
    ]);

    $story->bootScenarios();

    expect($test)->toBe([
        'creation' => [
            true, // run once
        ],
        'role' => [
            'admin', // run correct as_admin once
        ],
        'blocked' => [
            true, // run once
        ],
        'variable' => [
            'Something cool', // callback run with parameter correctly
        ],
    ]);
});
