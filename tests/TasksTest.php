<?php

use BradieTilley\StoryBoard\Exceptions\TaskNotFoundException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Task;
use Illuminate\Support\Collection;

test('storyboard tasks are run when bootTasks is run', function () {
    $test = [
        'before' => [],
        'task' => [],
        'after' => [],
        'checks' => [],
    ];
    
    $story = Story::make()
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

    $story->bootTask();

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
        ->name('test')
        ->task('one')
        ->task('two')
        ->task('three')
        ->task('four')
        ->bootTask();

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
        ->task($task)
        ->task('registered')
        ->task(function () use ($tasksRun) {
            $tasksRun[] = 'inline';
        })
        ->bootTask();
    
    expect($tasksRun->toArray())->toBe([
        'registered',
        'variable',
        'inline',
    ]);
});
