<?php

use BradieTilley\StoryBoard\Exceptions\TaskGeneratorNotFoundException;
use BradieTilley\StoryBoard\Exceptions\TaskNotFoundException;
use BradieTilley\StoryBoard\Exceptions\TaskNotSpecifiedException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Task;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

test('storyboard tasks are run when bootTaskss is run', function () {
    $test = [
        'before' => [],
        'task' => [],
        'after' => [],
        'checks' => [],
    ];

    $story = Story::make()
        ->can()
        ->before(function () use (&$test) {
            $test['before'][] = 'run1';
        })
        ->task(function () use (&$test) {
            $test['task'][] = 'run2';
        })
        ->after(function () use (&$test) {
            $test['after'][] = 'run3';
        })
        ->check(
            can: function () use (&$test) {
                $test['checks'][] = 'can';
            },
            cannot: function () use (&$test) {
                $test['checks'][] = 'cannot';
            },
        );

    $story->registerTasks()->bootTasks();

    expect($test)->toBe([
        'before' => [
            'run1',
        ],
        'task' => [
            'run2',
        ],
        'after' => [
            'run3',
        ],
        'checks' => [],
    ]);

    $story->can()->assert();

    expect($test)->toBe([
        'before' => [
            'run1',
        ],
        'task' => [
            'run2',
        ],
        'after' => [
            'run3',
        ],
        'checks' => [
            'can',
        ],
    ]);

    $test['checks'] = [];

    $story->cannot()->assert();

    expect($test)->toBe([
        'before' => [
            'run1',
        ],
        'task' => [
            'run2',
        ],
        'after' => [
            'run3',
        ],
        'checks' => [
            'cannot',
        ],
    ]);
});

test('tasks can be booted in a custom order', function () {
    $data = collect();

    Task::make('one', fn () => $data->push('3'))->order(3);
    Task::make('two', fn () => $data->push('1'))->order(1);
    Task::make('three', fn () => $data->push('4'))->order(4);
    Task::make('four', fn () => $data->push('2'))->order(2);

    Story::make()
        ->can()
        ->task(fn () => null)
        ->check(fn () => null)
        ->name('test')
        ->task('one')
        ->task('two')
        ->task('three')
        ->task('four')
        ->registerTasks()
        ->bootTasks();

    expect($data->toArray())->toBe([
        '1',
        '2',
        '3',
        '4',
    ]);
});

test('an exception is thrown when a task is referenced but not found', function () {
    Task::make('found', fn () => null);

    Story::make()->task('found')->task('not_found')->boot();
})->throws(TaskNotFoundException::class, 'The `not_found` task could not be found.');

test('tasks can be defined as inline closures, Task objects, or string identifiers', function () {
    $tasksRun = Collection::make();

    Task::make('registered', function () use ($tasksRun) {
        $tasksRun[] = 'registered';
    });

    $task = new Task('variable', function () use ($tasksRun) {
        $tasksRun[] = 'variable';
    });

    Story::make()
        ->can()
        ->task(fn () => null)
        ->check(fn () => null)
        ->task($task)
        ->task('registered')
        ->task(function () use ($tasksRun) {
            $tasksRun[] = 'inline';
        })
        ->registerTasks()
        ->bootTasks();

    expect($tasksRun->toArray())->toBe([
        'registered',
        'variable',
        'inline',
    ]);
});

test('a story must have at least one task', function () {
    $story = Story::make()
        ->check(fn () => true)
        ->can()
        ->name('parent')
        ->stories(
            Story::make('child'),
        );

    $all = $story->storiesAll;

    expect($all)->toHaveCount(1);
    /** @var Story $story */
    $story = $all->first();

    $story->boot()->assert();
})->throws(TaskNotSpecifiedException::class, 'No task was found for the story `parent child`');

test('all test callbacks can be inherited from parent story', function () {
    $ran = Collection::make([]);

    $board = StoryBoard::make()
        ->name('parent')
        ->can()
        ->check(
            fn () => $ran[] = 'can:parent',
            fn () => $ran[] = 'cannot:parent',
        )
        ->before(fn () => $ran[] = 'before:parent')
        ->after(fn () => $ran[] = 'after:parent')
        ->task(fn () => $ran[] = 'task:parent')
        ->stories([
            Story::make('child a'),
            Story::make()
                ->name('child b')
                ->cannot()
                ->check(
                    fn () => $ran[] = 'can:child_b',
                    fn () => $ran[] = 'cannot:child_b',
                )
                ->before(fn () => $ran[] = 'before:child_b')
                ->after(fn () => $ran[] = 'after:child_b')
                ->task(fn () => $ran[] = 'task:child_b'),
            Story::make('child c')->stories([
                Story::make('child c1'),
                Story::make()
                    ->name('child c2')
                    ->check(
                        fn () => $ran[] = 'can:child_c2',
                        fn () => $ran[] = 'cannot:child_c2',
                    )
                    ->before(fn () => $ran[] = 'before:child_c2')
                    ->after(fn () => $ran[] = 'after:child_c2')
                    ->task(fn () => $ran[] = 'task:child_c2'),
            ]),
        ]);

    foreach ($board->allStories() as $story) {
        $story->boot()->assert();
    }

    expect($ran->toArray())->toBe([
        // child a
        'before:parent',
        'task:parent',
        'after:parent',
        'can:parent', // can
        // child b
        'before:child_b',
        'task:parent', // parent and child b task
        'task:child_b',
        'after:child_b',
        'cannot:child_b', // cannot
        // child c1
        'before:parent',
        'task:parent',
        'after:parent',
        'can:parent', // can
        // child c2
        'before:child_c2',
        'task:parent', // parent and child b task
        'task:child_c2',
        'after:child_c2',
        'can:child_c2', // can
    ]);
});

test('tasks that are missing a generator throw an exception when booted', function () {
    $ran = Collection::make([]);

    Task::make('something_cooler')->as(fn () => $ran[] = 'yes');
    Task::make('something_cool');

    $story = Story::make()
        ->can()
        ->check(fn () => null)
        ->task('something_cooler')
        ->task('something_cool');

    // The task 'something_cooler' boots correctly
    // The task 'something_cool' does not (no generator)
    $story->boot();
})->throws(TaskGeneratorNotFoundException::class, 'The `something_cool` task generator callback could not be found.');

test('you may create a story with an assertion and unset the assertion for a child story', function () {
    $story = StoryBoard::make()
        ->name('parent')
        ->can()
        ->stories([
            // can: inherits from 'parent'
            Story::make('child can implicit'),
            // can: overrwides from 'parent' (no affect really)
            Story::make()->can()->name('child can explicit'),
            // cannot: overrwides from 'parent'
            Story::make()->cannot()->name('child cannot explicit'),
            // null: overrwides from 'parent'
            Story::make()->noAssertion()->name('child unset')->stories([
                // null: inherits from 'child unset'
                Story::make('grandchild null implicit'),
                // can: overrides noAssertion from 'child unset'
                Story::make()->can()->name('grandchild can explicit'),
                // cannot: overrides noAssertion from 'child unset'
                Story::make()->cannot()->name('grandchild cannot explicit'),
            ]),
        ]);

    $actual = $story->storiesAll->keys()->toArray();

    $expect = [
        '[Can] parent child can implicit',
        '[Can] parent child can explicit',
        '[Cannot] parent child cannot explicit',
        '[Can] parent child unset grandchild null implicit',
        '[Can] parent child unset grandchild can explicit',
        '[Cannot] parent child unset grandchild cannot explicit',
    ];

    expect($actual)->toBe($expect);
});