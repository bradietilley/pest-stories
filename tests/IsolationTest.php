<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

test('a story can isolate itself and prevent other stories from running', function (string $story) {
    Story::flushIsolation();
    $runs = collect();

    $storyA = Story::make()->name('A')->task(fn () => $runs[] = 'A')->check(fn () => true)->can();
    $storyB = Story::make()->name('B')->task(fn () => $runs[] = 'B')->check(fn () => true)->can();

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
        ->task(fn (Story $story) => $ran[] = $story->getName())
        ->check(fn () => true)
        ->stories([
            Story::make()->name('child 1')->stories([
                Story::make()->name('child 1a'),
                Story::make()->name('child 1b'),
                Story::make()->name('child 1c')->stories([
                    Story::make()->name('child 1c1'),
                    Story::make()->name('child 1c2'),
                ]),
            ])->isolate(),
            Story::make()->name('child 2')->stories([
                Story::make()->name('child 2a'),
                Story::make()->name('child 2b'),
                Story::make()->name('child 2c')->stories([
                    Story::make()->name('child 2c1'),
                    Story::make()->name('child 2c2'),
                ]),
            ]),
        ])
        ->allStories();

    foreach ($stories as $story) {
        $story->boot()->assert();
    }

    expect($ran->toArray())->toBe([
        'child 1a',
        'child 1b',
        'child 1c1',
        'child 1c2',
    ]);
});

test('multiple stories can be isolated and all isolated stories will run', function () {
    $ran = Collection::make([]);

    $stories = StoryBoard::make()
        ->can()
        ->name('parent')
        ->task(fn (Story $story) => $ran[] = $story->getName())
        ->check(fn () => true)
        ->stories([
            Story::make()->name('child 1')->stories([
                Story::make()->name('child 1a'),
                Story::make()->name('child 1b'),
                Story::make()->name('child 1c')->stories([
                    Story::make()->name('child 1c1'),
                    Story::make()->name('child 1c2'),
                ]),
            ])->isolate(),
            Story::make()->name('child 2')->stories([
                Story::make()->name('child 2a'),
                Story::make()->name('child 2b'),
                Story::make()->name('child 2c')->stories([
                    Story::make()->name('child 2c1'),
                    Story::make()->name('child 2c2'),
                ]),
            ]),
            Story::make()->name('child 3')->stories([
                Story::make()->name('child 3a'),
                Story::make()->name('child 3b'),
                Story::make()->name('child 3c')->stories([
                    Story::make()->name('child 3c1'),
                    Story::make()->name('child 3c2'),
                ]),
            ])->isolate(),
        ])
        ->allStories();

    foreach ($stories as $story) {
        $story->boot()->assert();
    }

    expect($ran->toArray())->toBe([
        'child 1a',
        'child 1b',
        'child 1c1',
        'child 1c2',
        'child 3a',
        'child 3b',
        'child 3c1',
        'child 3c2',
    ]);
});