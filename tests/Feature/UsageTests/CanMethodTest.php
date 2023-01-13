<?php

use BradieTilley\StoryBoard\Story;

function createStoryBoardForCanMethod(int $level, &$names): Story
{
    return Story::make()
        ->name('do something')
        ->when(
            ($level === 1),
            fn (Story $story) => $story->can()->name("{$story->getName()}#"),
        )
        ->action(fn () => true)
        ->assert(
            fn (Story $story) => $names[] = $story->getTestName(),
            fn (Story $story) => $names[] = 'cannot',
        )
        ->stories([
            Story::make()
                ->name('foo')
                ->when(
                    ($level === 2),
                    fn (Story $story) => $story->can()->name("{$story->getName()}#"),
                )
                ->stories([
                    Story::make()
                        ->name('one')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->can()->name("{$story->getName()}#"),
                        ),
                    Story::make()
                        ->name('two')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->can()->name("{$story->getName()}#"),
                        ),
                ]),
            Story::make()
                ->name('bar')
                ->when(
                    ($level === 2),
                    fn (Story $story) => $story->can()->name("{$story->getName()}#"),
                )
                ->stories([
                    Story::make()
                        ->name('one')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->can()->name("{$story->getName()}#"),
                        ),
                    Story::make()
                        ->name('two')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->can()->name("{$story->getName()}#"),
                        ),
                ]),
        ]);
}

test('the can method can be applied at a grandparent story level', function () {
    $names = collect();
    $board = createStoryBoardForCanMethod(level: 1, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot()->perform();
    }

    expect($names->toArray())->toBe([
        '[Can] do something# foo one',
        '[Can] do something# foo two',
        '[Can] do something# bar one',
        '[Can] do something# bar two',
    ]);
});

test('the can method can be applied at a parent story level', function () {
    $names = collect();
    $board = createStoryBoardForCanMethod(level: 2, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot()->perform();
    }

    expect($names->toArray())->toBe([
        '[Can] do something foo# one',
        '[Can] do something foo# two',
        '[Can] do something bar# one',
        '[Can] do something bar# two',
    ]);
});

test('the can method can be applied at a child story level', function () {
    $names = collect();
    $board = createStoryBoardForCanMethod(level: 3, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot()->perform();
    }

    expect($names->toArray())->toBe([
        '[Can] do something foo one#',
        '[Can] do something foo two#',
        '[Can] do something bar one#',
        '[Can] do something bar two#',
    ]);
});