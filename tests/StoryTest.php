<?php

use BradieTilley\StoryBoard\Exceptions\InvalidStoryException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Scenario;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

beforeEach(function () {
    Scenario::make('allows_creation', fn () => true);
    Scenario::make('as_admin', fn () => true);
    Scenario::make('as_customer', fn () => true);
    Scenario::make('as_unblocked', fn () => true);
    Scenario::make('as_blocked', fn () => true);
});

test('a storyboard with a single story can generate test cases with names', function () {
    $storyboard = StoryBoard::make()->name('Can create something cool');
    $tests = $storyboard->allStories();

    expect($tests)->toHaveCount(1)->toHaveKey('Can create something cool');
    expect($tests['Can create something cool'])->toBeInstanceOf(Story::class);
});

test('a storyboard with multiple stories can generate test cases with names', function () {
    $storyboard = StoryBoard::make()
        ->name('create something cool')
        ->stories([
            Story::make()->name('as admin')->scenario('as_admin')->can(),
            Story::make()->name('as customer')->scenario('as_customer')->cannot(),
        ]);

    $tests = $storyboard->allStories();

    $expectedKeys = [
        '[Can] create something cool as admin',
        '[Cannot] create something cool as customer',
    ];

    expect($tests)->toHaveCount(count($expectedKeys));
    expect($tests)->toHaveKeys($expectedKeys);
});

test('a storyboard with multiple nested stories can generate test cases with names', function () {
    $storyboard = StoryBoard::make()
        ->name('create something cool')
        ->stories([
            Story::make()->name('as admin')->scenario('as_admin')->stories([
                Story::make()->name('if not blocked')->scenario('as_unblocked')->can(),
                Story::make()->name('if blocked')->scenario('as_blocked')->cannot(),
            ]),
            Story::make()->name('as customer')->scenario('as_customer')->stories([
                Story::make()->name('if not blocked')->scenario('as_unblocked')->cannot(),
                Story::make()->name('if blocked')->scenario('as_blocked')->cannot(),
            ]),
        ]);

    $tests = $storyboard->allStories();

    $expectedKeys = [
        '[Can] create something cool as admin if not blocked',
        '[Cannot] create something cool as admin if blocked',
        '[Cannot] create something cool as customer if not blocked',
        '[Cannot] create something cool as customer if blocked',
    ];

    expect($tests)->toHaveCount(count($expectedKeys));
    expect($tests)->toHaveKeys($expectedKeys);
});

test('a storyboard with multiple nested stories can collate required scenarios', function () {
    $storyboard = StoryBoard::make()
        ->name('create something cool')
        ->scenario('allows_creation')
        ->stories([
            Story::make()->name('as admin')->scenario('as_admin')->stories([
                Story::make()->name('if not blocked')->scenario('as_unblocked')->can(),
                Story::make()->name('if blocked')->scenario('as_blocked')->cannot(),
            ]),
            Story::make()->name('as customer')->scenario('as_customer')->stories([
                Story::make()->name('if not blocked')->scenario('as_unblocked')->cannot(),
                Story::make()->name('if blocked')->scenario('as_blocked')->cannot(),
            ]),
        ]);

    $tests = $storyboard->allStories();

    $expect = [
        '[Can] create something cool as admin if not blocked' => [
            'allows_creation',
            'as_admin',
            'as_unblocked',
        ],
        '[Cannot] create something cool as admin if blocked' => [
            'allows_creation',
            'as_admin',
            'as_blocked',
        ],
        '[Cannot] create something cool as customer if not blocked' => [
            'allows_creation',
            'as_customer',
            'as_unblocked',
        ],
        '[Cannot] create something cool as customer if blocked' => [
            'allows_creation',
            'as_customer',
            'as_blocked',
        ],
    ];
    $actual = [];

    foreach ($tests as $key => $story) {
        $scenarios = array_keys($story->allScenarios());

        $actual[$key] = $scenarios;
    }

    expect($actual)->toBe($expect);
});

test('a story cannot accept children that are not story classes', function (string $type) {
    $stories = match ($type) {
        'string' => [
            'test',
        ],
        'scenario' => [
            Scenario::make('test', fn () => true),
        ],
        'mixed' => [
            Story::make()->name('test'),
            'test',
        ],
    };

    StoryBoard::make()->stories($stories);
})->with([
    'when given string' => 'string',
    'when given scenario' => 'scenario',
    'when given a story and a string' => 'mixed',
])->throws(InvalidStoryException::class, 'You must only provide Story classes to the stories() method.');

test('a story can fetch its children stories via collection methods and property proxies', function () {
    $story = Story::make()
        ->name('parent')
        ->can()
        ->check(fn () => null)
        ->task(fn () => null)
        ->stories([
            Story::make()->name('child 1'),
            Story::make()->name('child 2')->stories([
                Story::make()->name('child 2a'),
                Story::make()->name('child 2b'),
            ]),
        ]);

    $stories = $story->storiesDirect;
    expect($stories)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    expect($stories->map(fn (Story $story) => $story->getName())->toArray())->toBe([
        'child 1',
        'child 2',
    ]);

    $stories = $story->storiesAll;
    expect($stories)->toBeInstanceOf(Collection::class)->toHaveCount(3);
    expect($stories->map(fn (Story $story) => $story->getName())->values()->toArray())->toBe([
        'child 1',
        'child 2a',
        'child 2b',
    ]);

    // throws error
    $error = null;
    try {
        $story->something_doesnt_exist;
    } catch (\Throwable $error) {
    }

    expect($error)->not()->toBeNull()
        ->and($error->getMessage())->toBe('Undefined property: BradieTilley\StoryBoard\Story::$something_doesnt_exist');
});
