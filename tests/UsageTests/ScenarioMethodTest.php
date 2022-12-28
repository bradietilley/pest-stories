<?php

use BradieTilley\StoryBoard\Story\Scenario;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;

function createStoryBoardForMethodTest(int $level, &$names): StoryBoard
{
    Scenario::make('scenario_1', fn () => null, 'scenario1');
    Scenario::make('scenario_2a', fn () => null, 'scenario2');
    Scenario::make('scenario_2b', fn () => null, 'scenario2');
    Scenario::make('scenario_3a', fn () => null, 'scenario3');
    Scenario::make('scenario_3b', fn () => null, 'scenario3');
    Scenario::make('scenario_3c', fn () => null, 'scenario3');
    Scenario::make('scenario_3d', fn () => null, 'scenario3');

    return StoryBoard::make()
        ->name('do something')
        ->can()
        ->when(
            ($level === 1),
            fn (Story $story) => $story->scenario('scenario_1')->name("{$story->getName()}#"),
        )
        ->task(fn () => true)
        ->check(
            fn (Story $story) => $names[] = $story->getFullName(),
            fn (Story $story) => $names[] = 'cannot',
        )
        ->stories([
            Story::make()
                ->name('foo')
                ->when(
                    ($level === 2),
                    fn (Story $story) => $story->scenario('scenario_2a')->name("{$story->getName()}#"),
                )
                ->stories([
                    Story::make()
                        ->name('one')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->scenario('scenario_3a')->name("{$story->getName()}#"),
                        ),
                    Story::make()
                        ->name('two')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->scenario('scenario_3b')->name("{$story->getName()}#"),
                        ),
                ]),
            Story::make()
                ->name('bar')
                ->when(
                    ($level === 2),
                    fn (Story $story) => $story->scenario('scenario_2b')->name("{$story->getName()}#"),
                )
                ->stories([
                    Story::make()
                        ->name('one')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->scenario('scenario_3c')->name("{$story->getName()}#"),
                        ),
                    Story::make()
                        ->name('two')
                        ->when(
                            ($level === 3),
                            fn (Story $story) => $story->scenario('scenario_3d')->name("{$story->getName()}#"),
                        ),
                ]),
        ]);
}

test('the scenario method can be applied at a grandparent story level', function () {
    $names = collect();
    $board = createStoryBoardForMethodTest(level: 1, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot();

        $names[$story->getFullName()] = array_keys($story->allScenarios());
    }

    expect($names->toArray())->toBe([
        '[Can] do something# foo one' => [
            'scenario_1',
        ],
        '[Can] do something# foo two' => [
            'scenario_1',
        ],
        '[Can] do something# bar one' => [
            'scenario_1',
        ],
        '[Can] do something# bar two' => [
            'scenario_1',
        ],
    ]);
});

test('the scenario method can be applied at a parent story level', function () {
    $names = collect();
    $board = createStoryBoardForMethodTest(level: 2, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot();

        $names[$story->getFullName()] = array_keys($story->allScenarios());
    }

    expect($names->toArray())->toBe([
        '[Can] do something foo# one' => [
            'scenario_2a',
        ],
        '[Can] do something foo# two' => [
            'scenario_2a',
        ],
        '[Can] do something bar# one' => [
            'scenario_2b',
        ],
        '[Can] do something bar# two' => [
            'scenario_2b',
        ],
    ]);
});

test('the can method can be applied at a child story level', function () {
    $names = collect();
    $board = createStoryBoardForMethodTest(level: 3, names: $names);

    foreach ($board->allStories() as $story) {
        $story->boot();

        $names[$story->getFullName()] = array_keys($story->allScenarios());
    }

    expect($names->toArray())->toBe([
        '[Can] do something foo one#' => [
            'scenario_3a'
        ],
        '[Can] do something foo two#' => [
            'scenario_3b'
        ],
        '[Can] do something bar one#' => [
            'scenario_3c'
        ],
        '[Can] do something bar two#' => [
            'scenario_3d'
        ],
    ]);
});
