<?php

use BradieTilley\StoryBoard\Story;

test('nested stories will automatically boot when the parent is booted', function () {
    $stories = collect();

    $stories['test'] = Story::make('test')
        ->action(fn () => null)
        ->can(fn () => null)
        ->stories([
            $stories['A'] = Story::make('A'),
            $stories['B'] = Story::make('B')->stories([
                $stories['B1'] = Story::make('B1'),
                $stories['B2'] = Story::make('B2'),
            ]),
            $stories['C'] = Story::make('C'),
        ]);

    $result = $stories->map(fn (Story $story) => $story->alreadyRunSafe('boot'));
    expect($result->unique()->values()->toArray())->toBe([false]);

    $stories['test']->boot();

    unset($stories['test']);
    unset($stories['B']);

    $result = $stories->map(fn (Story $story) => $story->alreadyRunSafe('boot'));
    expect($result->unique()->values()->toArray())->toBe([true]);
});
