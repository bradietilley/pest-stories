<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

test('a story can isolate itself and prevent other stories from running', function (string $story) {
    $runs = collect();

    $storyA = Story::make('A')->task(fn () => $runs[] = 'A')->check(fn () => true)->can();
    $storyB = Story::make('B')->task(fn () => $runs[] = 'B')->check(fn () => true)->can();

    if ($story === 'A') {
        $storyA->isolate();
    } elseif ($story === 'B') {
        $storyB->isolate();
    } else {
        $ab = true;
        // none
    }

    $storyA->boot()->assert();
    $storyB->boot()->assert();

    $expect = [
        $story,
    ];

    if ($story === 'none') {
        $expect = [
            'A',
            'B',
        ];
    }

    expect($runs->toArray())->toBe($expect);
    
    Story::flushIsolation();
})->with([
    'A',
    'B',
    'none',
]);

test('a story can isolate itself and allow its children to also run', function () {
    $ran = Collection::make([]);

    $stories = StoryBoard::make()
        ->can()
        ->name('parent')
        ->task(fn (Story $story) => $ran[] = $story->getFullName())
        ->check(fn () => true)
        ->stories([
            Story::make('child 1')->stories([
                Story::make('child 1a'),
                Story::make('child 1b'),
                Story::make('child 1c')->stories([
                    Story::make('child 1c1'),
                    Story::make('child 1c2'),
                ]),
            ])->isolate(),
            Story::make('child 2')->stories([
                Story::make('child 2a'),
                Story::make('child 2b'),
                Story::make('child 2c')->stories([
                    Story::make('child 2c1'),
                    Story::make('child 2c2'),
                ]),
            ]),
        ])
        ->allStories();

    foreach ($stories as $story) {
        $story->boot()->assert();
    }

    expect($ran->toArray())->toBe([
        'parent child 1 child 1a',
        'parent child 1 child 1b',
        'parent child 1 child 1c child 1c1',
        'parent child 1 child 1c child 1c2',
    ]);

    Story::flushIsolation();
});

test('multiple stories can be isolated and all isolated stories will run', function () {
    $ran = Collection::make([]);

    $stories = StoryBoard::make()
        ->can()
        ->name('parent')
        ->task(fn (Story $story) => $ran[] = $story->getFullName())
        ->check(fn () => true)
        ->stories([
            Story::make('child 1')->stories([
                Story::make('child 1a'),
                Story::make('child 1b'),
                Story::make('child 1c')->stories([
                    Story::make('child 1c1'),
                    Story::make('child 1c2'),
                ]),
            ])->isolate(),
            Story::make('child 2')->stories([
                Story::make('child 2a'),
                Story::make('child 2b'),
                Story::make('child 2c')->stories([
                    Story::make('child 2c1'),
                    Story::make('child 2c2'),
                ]),
            ]),
            Story::make('child 3')->stories([
                Story::make('child 3a'),
                Story::make('child 3b'),
                Story::make('child 3c')->stories([
                    Story::make('child 3c1'),
                    Story::make('child 3c2'),
                ]),
            ])->isolate(),
        ])
        ->allStories();

    foreach ($stories as $story) {
        $story->boot()->assert();
    }

    expect($ran->toArray())->toBe([
        'parent child 1 child 1a',
        'parent child 1 child 1b',
        'parent child 1 child 1c child 1c1',
        'parent child 1 child 1c child 1c2',
        'parent child 3 child 3a',
        'parent child 3 child 3b',
        'parent child 3 child 3c child 3c1',
        'parent child 3 child 3c child 3c2',
    ]);
    
    Story::flushIsolation();
});
