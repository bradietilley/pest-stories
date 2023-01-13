<?php

use BradieTilley\StoryBoard\Exceptions\ActionGeneratorNotFoundException;
use BradieTilley\StoryBoard\Exceptions\ActionNotFoundException;
use BradieTilley\StoryBoard\Exceptions\ActionNotSpecifiedException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\AbstractAction;
use BradieTilley\StoryBoard\Story\Action;
use Illuminate\Support\Collection;

test('a storyboard with multiple nested stories can collate required actions', function () {
    Action::make('allows_creation', fn () => true);
    Action::make('as_admin', fn () => true);
    Action::make('as_customer', fn () => true);
    Action::make('as_unblocked', fn () => true);
    Action::make('as_blocked', fn () => true);

    $storyboard = Story::make()
        ->can()
        ->assert(fn () => null)
        ->name('create something cool')
        ->action('allows_creation')
        ->stories([
            Story::make('as admin')->action('as_admin')->stories([
                Story::make('if not blocked')->action('as_unblocked')->can(),
                Story::make('if blocked')->action('as_blocked')->cannot(),
            ]),
            Story::make('as customer')->action('as_customer')->stories([
                Story::make('if not blocked')->action('as_unblocked')->cannot(),
                Story::make('if blocked')->action('as_blocked')->cannot(),
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
        $actions = array_keys($story->allActions());

        $actual[$key] = $actions;
    }

    expect($actual)->toBe($expect);
});

test('action callbacks are executed when a story boots its actions', function () {
    $test = [
        'creation' => [],
        'role' => [],
        'blocked' => [],
        'variable' => [],
    ];

    Action::make('allows_creation', function () use (&$test) {
        $test['creation'][] = true;
    }, 'creation');
    Action::make('as_admin', function () use (&$test) {
        $test['role'][] = 'admin';
    }, 'role');
    Action::make('as_customer', function () use (&$test) {
        $test['role'][] = 'customer';
    }, 'role');
    Action::make('as_blocked', function () use (&$test) {
        $test['blocked'][] = true;
    }, 'blocked');
    Action::make('as_unblocked', function () use (&$test) {
        $test['blocked'][] = false;
    }, 'blocked');
    Action::make('with_variable', function (string $name) use (&$test) {
        $test['variable'][] = $name;
    }, 'var');

    $story = Story::make()
        ->action('allows_creation')
        ->action('as_admin')
        ->action('as_blocked')
        ->action('with_variable', [
            'name' => 'Something cool',
        ]);

    $story->registerActions();
    $actions = $story->allActions();

    foreach ($actions as $action => $storyAction) {
        $actions[$action] = $storyAction->getArguments();
    }

    expect($actions)->toBe([
        'allows_creation' => [],
        'as_admin' => [],
        'as_blocked' => [],
        'with_variable' => [
            'name' => 'Something cool',
        ],
    ]);

    $story->bootActions();

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

test('action variables are made accessible to the check() callback', function () {
    Action::make('as_admin', fn () => 'ROLE::admin', 'role');
    Action::make('as_blocked')->as(fn () => 'is blocked')->variable('blocked');

    $data = [];

    $story = Story::make()
        ->can()
        ->name('do something')
        ->action('as_admin')
        ->action('as_blocked')
        ->assert(function ($role, $blocked) use (&$data) {
            $data['check_role'] = $role;
            $data['check_blocked'] = $blocked;
        });

    $story->boot()->perform();

    expect($data)->toBe([
        'check_role' => 'ROLE::admin',
        'check_blocked' => 'is blocked',
    ]);
});

test('actions can be booted in a custom order', function () {
    $data = collect();

    Action::make('one', fn () => $data->push('3'), 'dataone')->order(3);
    Action::make('two', fn () => $data->push('1'), 'datatwo')->order(1);
    Action::make('three', fn () => $data->push('4'), 'datathree')->order(4);
    Action::make('four', fn () => $data->push('2'), 'datafour')->order(2);

    Story::make()
        ->name('test')
        ->action('one')
        ->action('two')
        ->action('three')
        ->action('four')
        ->registerActions()
        ->bootActions();

    expect($data->toArray())->toBe([
        '1',
        '2',
        '3',
        '4',
    ]);
});

test('an exception is thrown when a action is referenced but not found', function () {
    Action::make('found', fn () => null, 'var');

    Story::make()->action('found')->action('not_found')->boot();
})->throws(ActionNotFoundException::class, 'The `not_found` action could not be found.');

// test('actions can be defined as inline closures, Action objects, or string identifiers', function () {
//     $actionsRun = Collection::make();

//     Action::make('registered', function ($a) use ($actionsRun) {
//         $actionsRun[] = 'registered_'.$a;
//     });

//     $action = new Action('variable', function ($a) use ($actionsRun) {
//         $actionsRun[] = 'variable_'.$a;
//     });

//     Story::make()
//         ->action($action, ['a' => '1'])
//         ->action('registered', ['a' => '2'])
//         ->action(function ($a) use ($actionsRun) {
//             $actionsRun[] = 'inline_'.$a;
//         }, ['a' => '3'])
//         ->registerActions()
//         ->bootActions();

//     expect($actionsRun->toArray())->toBe([
//         'registered_2',
//         'variable_1',
//         'inline_3',
//     ]);
// });

test('actions can offer to append their name to the story name', function () {
    Action::make('test_a', fn () => null);
    Action::make('test_b', fn () => null)->appendName('custom name');
    Action::make('test_c', fn () => null)->appendName();

    $story = Story::make()
        ->name('parent name')
        ->can()
        ->assert(fn () => true)
        ->stories([
            Story::make('existing name')->action('test_a'), // parent name existing name
            Story::make('existing name')->action('test_b'), // parent name existing name custom name
            Story::make('existing name')->action('test_c'), // parent name existing name test c
            Story::make()->action('test_b'),                        // parent name custom name
            Story::make()->action('test_c'),                        // parent name test c
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

test('actions that are missing a generator throw an exception when booted', function () {
    $ran = Collection::make([]);

    Action::make('something_cooler')->as(fn () => $ran[] = 'yes');
    Action::make('something_cool');

    $story = Story::make()
        ->can()
        ->assert(fn () => null)
        ->action('something_cooler')
        ->action('something_cool');

    // The action 'something_cooler' boots correctly
    // The action 'something_cool' does not (no generator)
    $story->boot();
})->throws(ActionGeneratorNotFoundException::class, 'The `something_cool` action generator callback could not be found.');

test('a story with the multiple actions of the same variable will use the last/most-child one', function () {
    Action::make('location_1')->as(fn () => '1')->variable('location')->order(1);
    Action::make('location_2')->as(fn () => '2')->variable('location')->order(1);
    Action::make('location_3')->as(fn () => '3')->variable('location')->order(1);
    Action::make('location_4')->as(fn () => '4')->variable('location')->order(1);

    $data = Collection::make([]);

    Story::make()
        ->action('location_1')
        ->can()
        ->assert(fn (Story $story, string $location) => $data[] = $story->getName().':'.$location)
        ->stories([
            // Test inheritance
            Story::make('1'),
            // Test inheritance is ignored; duplicate at same level = last one taken
            Story::make()->action('location_1')->action('location_2')->name('2'),
            // Test inheritance is ignored; duplicate at same level = last one taken
            Story::make()->action('location_2')->action('location_3')->stories([
                Story::make('3'),
                Story::make()->action('location_4')->name('4'),
            ]),
        ])
        ->storiesAll
        ->each(fn (Story $story) => $story->boot()->perform());

    expect($data->toArray())->toBe([
        '1:1',
        '2:2',
        '3:3',
        '4:4',
    ]);
});

test('can set multiple actions of multiple types in a single function', function () {
    $ran = Collection::make();

    Action::make('old_object', fn ($arg = 'noarg') => $ran[] = "old_object:{$arg}");
    Action::make('old_object2', fn ($arg = 'noarg') => $ran[] = "old_object2:{$arg}");

    $story = Story::make()
        ->name('action test')
        ->actions([
            Action::make('new_object')->as(fn ($arg = 'noarg') => $ran[] = "new_object:{$arg}"),
            'old_object',
            fn ($arg = 'noarg') => $ran[] = "inline:{$arg}",
            'old_object2' => ['arg' => 'works'],
        ])
        ->can()
        ->assert(fn () => null)
        ->boot()
        ->perform();

    expect($ran->toArray())->toBe([
        'old_object:noarg',
        'old_object2:works',
        'new_object:noarg',
        'inline:noarg',
    ]);
});

test('action flush forgets all registered actions', function () {
    Action::make('something', fn () => true);

    // Works great by default
    expect(Action::fetch('something'))->toBeInstanceOf(Action::class);

    // Flush actions
    Action::flush();

    // But now can't fetch action (throws exception)
    try {
        Action::fetch('something');
        $this->fail();
    } catch (ActionNotFoundException $actionNotFound) {
    }

    // Remake action
    Action::make('something', fn () => true);

    // continues to work great
    expect(Action::fetch('something'))->toBeInstanceOf(Action::class);

    // Clear all all
    AbstractAction::flush();

    // But now can't fetch action (throws exception)
    try {
        Action::fetch('something');
        $this->fail();
    } catch (ActionNotFoundException $actionNotFound) {
    }
});

test('a action may have a custom registering and booting callback', function () {
    $data = Collection::make();

    Action::make('event_action')
        ->as(fn () => $data[] = 'generating')
        ->registering(fn () => $data[] = 'registering')
        ->booting(fn () => $data[] = 'booting');

    $story = Story::make('events test')
        ->action('event_action')
        ->can()
        ->assert(fn () => null);

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

test('a story must have at least one task', function () {
    $story = Story::make()
        ->assert(fn () => true)
        ->can()
        ->name('parent')
        ->stories(
            Story::make('child'),
        );

    $all = $story->storiesAll;

    expect($all)->toHaveCount(1);
    /** @var Story $story */
    $story = $all->first();

    $story->boot()->perform();
})->throws(ActionNotSpecifiedException::class, 'No action was found for the story `parent child`');

test('can fetch all registered actions', function () {
    Action::flush();

    Action::make('a')->as(fn () => null);
    Action::make('b')->as(fn () => null);
    Action::make('c')->as(fn () => null);

    expect(Action::all())
        ->toHaveCount(3)
        ->keys()
        ->toArray()
        ->toBe([
            'a',
            'b',
            'c',
        ]);
});