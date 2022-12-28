<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;

function createStoryBoardForTaskTest(?int $level, &$names): StoryBoard
{
    return StoryBoard::make()
        ->name('do something')
        ->can()
        ->check(
            can: fn () => null,
            cannot: fn () => null,
        )
        ->when(
            ($level === 1) || is_null($level),
            fn (Story $story) => $story->task(
                fn () => $names[] = 'task_1',
            ),
        )
        ->stories([
            Story::make()
                ->name('foo')
                ->when(
                    ($level === 2) || is_null($level),
                    fn (Story $story) => $story->task(
                        fn () => $names[] = 'task_2a',
                    ),
                )
                ->stories([
                    Story::make()
                        ->name('one')
                        ->when(
                            ($level === 3) || is_null($level),
                            fn (Story $story) => $story->task(
                                fn () => $names[] = 'task_3a'
                            )
                        ),
                    Story::make()
                        ->name('two')
                        ->when(
                            ($level === 3) || is_null($level),
                            fn (Story $story) => $story->task(
                                fn () => $names[] = 'task_3b'
                            )
                        ),
                ]),
            Story::make()
                ->name('bar')
                ->when(
                    ($level === 2) || is_null($level),
                    fn (Story $story) => $story->task(
                        fn () => $names[] = 'task_2b',
                    ),
                )
                ->stories([
                    Story::make()
                        ->name('one')
                        ->when(
                            ($level === 3) || is_null($level),
                            fn (Story $story) => $story->task(
                                fn () => $names[] = 'task_3c'
                            )
                        ),
                    Story::make()
                        ->name('two')
                        ->when(
                            ($level === 3) || is_null($level),
                            fn (Story $story) => $story->task(
                                fn () => $names[] = 'task_3d'
                            )
                        ),
                ]),
        ]);
}

test('the task method can be applied at a grandparent story level', function () {
    $names = collect();
    $board = createStoryBoardForTaskTest(level: 1, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot()->assert();
    }

    expect($names->toArray())->toBe([
        'task_1',
        'task_1',
        'task_1',
        'task_1',
    ]);
});

test('the task method can be applied at a parent story level', function () {
    $names = collect();
    $board = createStoryBoardForTaskTest(level: 2, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot()->assert();
    }

    expect($names->toArray())->toBe([
        'task_2a',
        'task_2a',
        'task_2b',
        'task_2b',
    ]);
});

test('the can method can be applied at a child story level', function () {
    $names = collect();
    $board = createStoryBoardForTaskTest(level: 3, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot()->assert();
    }

    expect($names->toArray())->toBe([
        'task_3a',
        'task_3b',
        'task_3c',
        'task_3d',
    ]);
});

test('the task method can be applied at multiple levels', function () {
    $names = collect();
    $board = createStoryBoardForTaskTest(level: null, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot()->assert();
    }

    expect($names->toArray())->toBe([
        'task_1',
        'task_2a',
        'task_3a',
        'task_1',
        'task_2a',
        'task_3b',
        'task_1',
        'task_2b',
        'task_3c',
        'task_1',
        'task_2b',
        'task_3d',
    ]);
});
