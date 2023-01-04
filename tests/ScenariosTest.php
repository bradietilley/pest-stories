<?php

use BradieTilley\StoryBoard\Exceptions\ScenarioGeneratorNotFoundException;
use BradieTilley\StoryBoard\Exceptions\ScenarioNotFoundException;
use BradieTilley\StoryBoard\Exceptions\TaskNotFoundException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\AbstractAction;
use BradieTilley\StoryBoard\Story\Scenario;
use BradieTilley\StoryBoard\Story\Task;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

test('a storyboard with multiple nested stories can collate required scenarios', function () {
    Scenario::make('allows_creation', fn () => true);
    Scenario::make('as_admin', fn () => true);
    Scenario::make('as_customer', fn () => true);
    Scenario::make('as_unblocked', fn () => true);
    Scenario::make('as_blocked', fn () => true);

    $storyboard = StoryBoard::make()
        ->can()
        ->task(fn () => null)
        ->check(fn () => null)
        ->name('create something cool')
        ->scenario('allows_creation')
        ->stories([
            Story::make('as admin')->scenario('as_admin')->stories([
                Story::make('if not blocked')->scenario('as_unblocked')->can(),
                Story::make('if blocked')->scenario('as_blocked')->cannot(),
            ]),
            Story::make('as customer')->scenario('as_customer')->stories([
                Story::make('if not blocked')->scenario('as_unblocked')->cannot(),
                Story::make('if blocked')->scenario('as_blocked')->cannot(),
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

    Scenario::make('allows_creation', function () use (&$test) {
        $test['creation'][] = true;
    }, 'creation');
    Scenario::make('as_admin', function () use (&$test) {
        $test['role'][] = 'admin';
    }, 'role');
    Scenario::make('as_customer', function () use (&$test) {
        $test['role'][] = 'customer';
    }, 'role');
    Scenario::make('as_blocked', function () use (&$test) {
        $test['blocked'][] = true;
    }, 'blocked');
    Scenario::make('as_unblocked', function () use (&$test) {
        $test['blocked'][] = false;
    }, 'blocked');
    Scenario::make('with_variable', function (string $name) use (&$test) {
        $test['variable'][] = $name;
    }, 'var');

    $story = Story::make()
        ->scenario('allows_creation')
        ->scenario('as_admin')
        ->scenario('as_blocked')
        ->scenario('with_variable', [
            'name' => 'Something cool',
        ]);

    $story->registerScenarios();
    $scenarios = $story->allScenarios();

    foreach ($scenarios as $scenario => $data) {
        $scenarios[$scenario] = $data['arguments'];
    }

    expect($scenarios)->toBe([
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
    Scenario::make('as_admin', fn () => 'ROLE::admin', 'role');
    Scenario::make('as_blocked')->as(fn () => 'is blocked')->variable('blocked');

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

    Scenario::make('one', fn () => $data->push('3'), 'dataone')->order(3);
    Scenario::make('two', fn () => $data->push('1'), 'datatwo')->order(1);
    Scenario::make('three', fn () => $data->push('4'), 'datathree')->order(4);
    Scenario::make('four', fn () => $data->push('2'), 'datafour')->order(2);

    Story::make()
        ->name('test')
        ->scenario('one')
        ->scenario('two')
        ->scenario('three')
        ->scenario('four')
        ->registerScenarios()
        ->bootScenarios();

    expect($data->toArray())->toBe([
        '1',
        '2',
        '3',
        '4',
    ]);
});

test('an exception is thrown when a scenario is referenced but not found', function () {
    Scenario::make('found', fn () => null, 'var');

    Story::make()->scenario('found')->scenario('not_found')->boot();
})->throws(ScenarioNotFoundException::class, 'The `not_found` scenario could not be found.');

test('scenarios can be defined as inline closures, Task objects, or string identifiers', function () {
    $tasksRun = Collection::make();

    Scenario::make('registered', function ($a) use ($tasksRun) {
        $tasksRun[] = 'registered_'.$a;
    });

    $scenario = new Scenario('variable', function ($a) use ($tasksRun) {
        $tasksRun[] = 'variable_'.$a;
    });

    Story::make()
        ->scenario($scenario, ['a' => '1'])
        ->scenario('registered', ['a' => '2'])
        ->scenario(function ($a) use ($tasksRun) {
            $tasksRun[] = 'inline_'.$a;
        }, ['a' => '3'])
        ->registerScenarios()
        ->bootScenarios();

    expect($tasksRun->toArray())->toBe([
        'registered_2',
        'variable_1',
        'inline_3',
    ]);
});

test('scenarios can offer to append their name to the story name', function () {
    Scenario::make('test_a', fn () => null);
    Scenario::make('test_b', fn () => null)->appendName('custom name');
    Scenario::make('test_c', fn () => null)->appendName();

    $story = StoryBoard::make()
        ->name('parent name')
        ->can()
        ->check(fn () => true)
        ->task(fn () => true)
        ->stories([
            Story::make('existing name')->scenario('test_a'), // parent name existing name
            Story::make('existing name')->scenario('test_b'), // parent name existing name custom name
            Story::make('existing name')->scenario('test_c'), // parent name existing name test c
            Story::make()->scenario('test_b'),                        // parent name custom name
            Story::make()->scenario('test_c'),                        // parent name test c
        ]);

    $stories = Collection::make($story->allStories())
        ->map(fn (Story $story) => $story->getTestName())
        ->values()
        ->all();

    expect($stories)->toBe([
        '[Can] parent name existing name',
        '[Can] parent name existing name custom name',
        '[Can] parent name existing name test c',
        '[Can] parent name custom name',
        '[Can] parent name test c',
    ]);
});

test('scenarios that are missing a generator throw an exception when booted', function () {
    $ran = Collection::make([]);

    Scenario::make('something_cooler')->as(fn () => $ran[] = 'yes');
    Scenario::make('something_cool');

    $story = Story::make()
        ->can()
        ->task(fn () => null)
        ->check(fn () => null)
        ->scenario('something_cooler')
        ->scenario('something_cool');

    // The scenario 'something_cooler' boots correctly
    // The scenario 'something_cool' does not (no generator)
    $story->boot();
})->throws(ScenarioGeneratorNotFoundException::class, 'The `something_cool` scenario generator callback could not be found.');


test('a story with the multiple scenarios of the same variable will use the last/most-child one', function () {
    Scenario::make('location_1')->as(fn () => '1')->variable('location')->order(1);
    Scenario::make('location_2')->as(fn () => '2')->variable('location')->order(1);
    Scenario::make('location_3')->as(fn () => '3')->variable('location')->order(1);
    Scenario::make('location_4')->as(fn () => '4')->variable('location')->order(1);

    $data = Collection::make([]);

    Story::make()
        ->scenario('location_1')
        ->can()
        ->before(fn (Story $story, string $location) => $data[] = $story->getName() . ':' . $location)
        ->task(fn (Story $story, string $location) => $data[] = $story->getName() . ':' . $location)
        ->after(fn (Story $story, string $location) => $data[] = $story->getName() . ':' . $location)
        ->check(fn (Story $story, string $location) => $data[] = $story->getName() . ':' . $location)
        ->stories([
            // Test inheritance
            Story::make('1'),
            // Test inheritance is ignored; duplicate at same level = last one taken
            Story::make()->scenario('location_1')->scenario('location_2')->name('2'),
            // Test inheritance is ignored; duplicate at same level = last one taken
            Story::make()->scenario('location_2')->scenario('location_3')->stories([
                Story::make('3'),
                Story::make()->scenario('location_4')->name('4'),
            ]),
        ])
        ->storiesAll
        ->each(fn (Story $story) => $story->boot()->assert());

    expect($data->toArray())->toBe([
        '1:1',
        '1:1',
        '1:1',
        '1:1',
        '2:2',
        '2:2',
        '2:2',
        '2:2',
        '3:3',
        '3:3',
        '3:3',
        '3:3',
        '4:4',
        '4:4',
        '4:4',
        '4:4',
    ]);
});

test('can set multiple scenarios of multiple types in a single function', function () {
    $ran = Collection::make();

    Scenario::make('old_object', fn ($arg = 'noarg') => $ran[] = "old_object:{$arg}");
    Scenario::make('old_object2', fn ($arg = 'noarg') => $ran[] = "old_object2:{$arg}");

    $story = Story::make()
        ->name('scenario test')
        ->scenarios([
            Scenario::make('new_object')->as(fn ($arg = 'noarg') => $ran[] = "new_object:{$arg}"),
            'old_object',
            fn ($arg = 'noarg') => $ran[] = "inline:{$arg}",
            'old_object2' => [ 'arg' => 'works', ],
        ])
        ->can()
        ->check(fn () => null)
        ->task(fn () => null)
        ->boot()
        ->assert();

    expect($ran->toArray())->toBe([
        'old_object:noarg',
        'old_object2:works',
        'new_object:noarg',
        'inline:noarg',
    ]);
});

test('scenarios and tasks can share the same name', function () {
    Task::make('something', fn () => true);
    Scenario::make('something', fn () => true);

    expect(Task::fetch('something'))->toBeInstanceOf(Task::class);
    expect(Scenario::fetch('something'))->toBeInstanceOf(Scenario::class);
});

test('scenario flush forgets all registered scenarios', function () {
    Task::make('something', fn () => true);
    Scenario::make('something', fn () => true);

    // Works great by default
    expect(Task::fetch('something'))->toBeInstanceOf(Task::class);
    expect(Scenario::fetch('something'))->toBeInstanceOf(Scenario::class);

    // Flush scenarios
    Scenario::flush();

    // Can still fetch Task
    expect(Task::fetch('something'))->toBeInstanceOf(Task::class);

    // But now can't fetch scenario (throws exception)
    $scenarioNotFound = null;
    try {
        Scenario::fetch('something');
    } catch (ScenarioNotFoundException $scenarioNotFound) {
    }
    // Expect error was thrown
    expect($scenarioNotFound)->not()->toBeNull();

    // Remake scenario
    Scenario::make('something', fn () => true);

    // Both continue to work great
    expect(Task::fetch('something'))->toBeInstanceOf(Task::class);
    expect(Scenario::fetch('something'))->toBeInstanceOf(Scenario::class);

    AbstractAction::flush();

    // Now we can't fetch task (throws exception)
    $taskNotFound = null;
    try {
        Task::fetch('something');
    } catch (TaskNotFoundException $taskNotFound) {
    }
    // Expect error was thrown
    expect($taskNotFound)->not()->toBeNull();

    // And can't fetch scenario either (throws exception)
    $scenarioNotFound = null;
    try {
        Scenario::fetch('something');
    } catch (ScenarioNotFoundException $scenarioNotFound) {
    }
    // Expect error was thrown
    expect($scenarioNotFound)->not()->toBeNull();
});

test('a scenario may have a custom registering and booting callback', function () {
    $data = Collection::make();
    
    Scenario::make('event_scenario')
        ->as(fn () => $data[] = 'generating')
        ->registering(fn () => $data[] = 'registering')
        ->booting(fn () => $data[] = 'booting');
    
    $story = Story::make('events test')
        ->scenario('event_scenario')
        ->can()
        ->check(fn () => null)
        ->task(fn () => null);

    expect($data->toArray())->toBe([]);

    $story->register();

    expect($data->toArray())->toBe([
        'registering',
    ]);

    $story->boot();

    expect($data->toArray())->toBe([
        'registering',
        'booting',
        'generating',
    ]);
});