<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;

function createStoryBoardForCheckTest(int $level, &$names): StoryBoard
{
    return StoryBoard::make()
        ->name('do something')
        ->can()
        ->when(
            ($level === 1),
            fn (Story $story) => $story->check(
                can: fn () => $names[] = 'can_1',
                cannot: fn () => $names[] = 'cannot_1',
            )->name("{$story->getName()}#"),
        )
        ->action(fn () => true)
        ->stories([
            Story::make()
                ->name('foo')
                ->when(
                    ($level === 2),
                    fn (Story $story) => $story->check(
                        can: fn () => $names[] = 'can_2a',
                        cannot: fn () => $names[] = 'cannot_2a',
                    )->name("{$story->getName()}#"),
                )
                ->stories([
                    Story::make()
                        ->name('one')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->check(
                                can: fn () => $names[] = 'can_3a',
                                cannot: fn () => $names[] = 'cannot_3a',
                            )->name("{$story->getName()}#"),
                        ),
                    Story::make()
                        ->name('two')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->check(
                                can: fn () => $names[] = 'can_3b',
                                cannot: fn () => $names[] = 'cannot_3b',
                            )->name("{$story->getName()}#"),
                        ),
                ]),
            Story::make()
                ->name('bar')
                ->when(
                    ($level === 2),
                    fn (Story $story) => $story->check(
                        can: fn () => $names[] = 'can_2b',
                        cannot: fn () => $names[] = 'cannot_2b',
                    )->name("{$story->getName()}#"),
                )
                ->stories([
                    Story::make()
                        ->name('one')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->check(
                                can: fn () => $names[] = 'can_3c',
                                cannot: fn () => $names[] = 'cannot_3c',
                            )->name("{$story->getName()}#"),
                        ),
                    Story::make()
                        ->name('two')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->check(
                                can: fn () => $names[] = 'can_3d',
                                cannot: fn () => $names[] = 'cannot_3d',
                            )->name("{$story->getName()}#"),
                        ),
                ]),
        ]);
}

test('the check method can be applied at a grandparent story level', function () {
    $names = collect();
    $board = createStoryBoardForCheckTest(level: 1, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot()->perform();
    }

    expect($names->toArray())->toBe([
        'can_1',
        'can_1',
        'can_1',
        'can_1',
    ]);
});

test('the check method can be applied at a parent story level', function () {
    $names = collect();
    $board = createStoryBoardForCheckTest(level: 2, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot()->perform();
    }

    expect($names->toArray())->toBe([
        'can_2a',
        'can_2a',
        'can_2b',
        'can_2b',
    ]);
});

test('the can method can be applied at a child story level', function () {
    $names = collect();
    $board = createStoryBoardForCheckTest(level: 3, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot()->perform();
    }

    expect($names->toArray())->toBe([
        'can_3a',
        'can_3b',
        'can_3c',
        'can_3d',
    ]);
});
