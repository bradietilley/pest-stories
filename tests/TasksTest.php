<?php

use BradieTilley\StoryBoard\Story;

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
