<?php

use BradieTilley\StoryBoard\Exceptions\ScenarioNotFoundException;
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
        $scenarios = array_keys($story->allScenarios());

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

    expect($story->allScenarios())->toBe([
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

test('scenario variables are made accessible to the task() and check() callbacks', function () {
    Scenario::make('as_admin', 'role', fn () => 'ROLE::admin');
    Scenario::make('as_blocked', 'blocked', fn () => 'is blocked');
   
    $data = [];
    
    $story = StoryBoard::make()
        ->can()
        ->name('do something')
        ->scenario('as_admin')
        ->scenario('as_blocked')
        ->task(function ($role, $blocked) use (&$data) {
            $data['task_role'] = $role;
            $data['task_blocked'] = $blocked;
        })
        ->check(function ($role, $blocked) use (&$data) {
            $data['check_role'] = $role;
            $data['check_blocked'] = $blocked;
        });

    $story->boot()->assert();
    
    expect($data)->toBe([
        'task_role' => 'ROLE::admin',
        'task_blocked' => 'is blocked',
        'check_role' => 'ROLE::admin',
        'check_blocked' => 'is blocked',
    ]);
});

test('scenarios can be booted in a custom order', function () {
    $data = collect();

    Scenario::make('one', 'dataone', fn () => $data->push('3'))->order(3);
    Scenario::make('two', 'datatwo', fn () => $data->push('1'))->order(1);
    Scenario::make('three', 'datathree', fn () => $data->push('4'))->order(4);
    Scenario::make('four', 'datafour', fn () => $data->push('2'))->order(2);

    Story::make()
        ->name('test')
        ->scenario('one')
        ->scenario('two')
        ->scenario('three')
        ->scenario('four')
        ->bootScenarios();

    expect($data->toArray())->toBe([
        '1',
        '2',
        '3',
        '4',
    ]);
});

test('an exception is thrown when a scenario is referenced but not found', function () {
    Scenario::make('found', 'var', fn () => null);

    Story::make()->scenario('found')->scenario('not found');
})->throws(ScenarioNotFoundException::class, 'The `not_found` scenario could not be found.');