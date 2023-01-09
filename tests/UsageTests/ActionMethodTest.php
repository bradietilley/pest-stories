<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

function createStoryBoardForMethodTest(int $level, &$names): StoryBoard
{
    Action::make('action_1', fn () => null, 'action1');
    Action::make('action_2a', fn () => null, 'action2');
    Action::make('action_2b', fn () => null, 'action2');
    Action::make('action_3a', fn () => null, 'action3');
    Action::make('action_3b', fn () => null, 'action3');
    Action::make('action_3c', fn () => null, 'action3');
    Action::make('action_3d', fn () => null, 'action3');

    return StoryBoard::make()
        ->name('do something')
        ->can()
        ->when(
            ($level === 1),
            fn (Story $story) => $story->action('action_1')->name("{$story->getName()}#"),
        )
        ->assert(
            fn (Story $story) => $names[] = $story->getTestName(),
            fn (Story $story) => $names[] = 'cannot',
        )
        ->stories([
            Story::make()
                ->name('foo')
                ->when(
                    ($level === 2),
                    fn (Story $story) => $story->action('action_2a')->name("{$story->getName()}#"),
                )
                ->stories([
                    Story::make()
                        ->name('one')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->action('action_3a')->name("{$story->getName()}#"),
                        ),
                    Story::make()
                        ->name('two')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->action('action_3b')->name("{$story->getName()}#"),
                        ),
                ]),
            Story::make()
                ->name('bar')
                ->when(
                    ($level === 2),
                    fn (Story $story) => $story->action('action_2b')->name("{$story->getName()}#"),
                )
                ->stories([
                    Story::make()
                        ->name('one')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->action('action_3c')->name("{$story->getName()}#"),
                        ),
                    Story::make()
                        ->name('two')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->action('action_3d')->name("{$story->getName()}#"),
                        ),
                ]),
        ]);
}

test('the action method can be applied at a grandparent story level', function () {
    $names = collect();
    $board = createStoryBoardForMethodTest(level: 1, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot();

        $names[$story->getTestName()] = array_keys($story->allActions());
    }

    expect($names->toArray())->toBe([
        '[Can] do something# foo one' => [
            'action_1',
        ],
        '[Can] do something# foo two' => [
            'action_1',
        ],
        '[Can] do something# bar one' => [
            'action_1',
        ],
        '[Can] do something# bar two' => [
            'action_1',
        ],
    ]);
});

test('the action method can be applied at a parent story level', function () {
    $names = collect();
    $board = createStoryBoardForMethodTest(level: 2, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot();

        $names[$story->getTestName()] = array_keys($story->allActions());
    }

    expect($names->toArray())->toBe([
        '[Can] do something foo# one' => [
            'action_2a',
        ],
        '[Can] do something foo# two' => [
            'action_2a',
        ],
        '[Can] do something bar# one' => [
            'action_2b',
        ],
        '[Can] do something bar# two' => [
            'action_2b',
        ],
    ]);
});

test('the can method can be applied at a child story level', function () {
    $names = collect();
    $board = createStoryBoardForMethodTest(level: 3, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot();

        $names[$story->getTestName()] = array_keys($story->allActions());
    }

    expect($names->toArray())->toBe([
        '[Can] do something foo one#' => [
            'action_3a',
        ],
        '[Can] do something foo two#' => [
            'action_3b',
        ],
        '[Can] do something bar one#' => [
            'action_3c',
        ],
        '[Can] do something bar two#' => [
            'action_3d',
        ],
    ]);
});

test('action ordering is as per the actions pre-defined orders', function () {
    Action::flush();
    $actions = Collection::make();
    
    Action::make('a')->as(fn () => $actions[] = 'a');
    Action::make('b')->as(fn () => $actions[] = 'b');
    Action::make('e')->as(fn () => $actions[] = 'e')->order(5);
    Action::make('d')->as(fn () => $actions[] = 'd')->order(5);
    Action::make('c')->as(fn () => $actions[] = 'c')->order(4);

    $story = Story::make()
        ->can()
        ->assert(fn () => null)
        ->action('c')
        ->actions([
            'd' => [],
            'a' => [],
        ])
        ->stories([
            Story::make('child')->action('b'),
        ]);
    
    $story->storiesAll->first()->run();

    expect($actions->toArray())->toBe([
        'a',
        'b',
        'c',
        'd',
    ]);
});
