<?php

use BradieTilley\StoryBoard\Exceptions\InvalidStoryProvidedException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;
use Illuminate\Support\Collection;

beforeEach(function () {
    Action::make('allows_creation', fn () => true);
    Action::make('as_admin', fn () => true);
    Action::make('as_customer', fn () => true);
    Action::make('as_unblocked', fn () => true);
    Action::make('as_blocked', fn () => true);
});

test('a storyboard with a single story can generate test cases with names', function () {
    $storyboard = Story::make('create something cool')
        ->can()
        ->assert(fn () => null)
        ->action(fn () => null);

    $tests = $storyboard->allStories();
    expect($tests)->toHaveCount(0);

    $storyboard->stories(Story::make());
    $tests = $storyboard->allStories();

    expect($tests)->toHaveCount(1)->toHaveKey('[Can] create something cool');
    expect($tests['[Can] create something cool'])->toBeInstanceOf(Story::class);
});

test('a storyboard with multiple stories can generate test cases with names', function () {
    $storyboard = Story::make()
        ->name('create something cool')
        ->can()
        ->assert(fn () => null)
        ->action(fn () => null)
        ->stories([
            Story::make('as admin')->action('as_admin')->can(),
            Story::make('as customer')->action('as_customer')->cannot(),
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
    $storyboard = Story::make()
        ->name('create something cool')
        ->can()
        ->assert(fn () => null)
        ->action(fn () => null)
        ->stories([
            Story::make('as admin')->action('as_admin')->stories([
                Story::make('if not blocked')->action('as_unblocked')->can(),
                Story::make('if blocked')->action('as_blocked')->cannot(),
            ]),
            Story::make('as customer')->action('as_customer')->stories([
                Story::make('if not blocked')->action('as_unblocked')->cannot(),
                Story::make('if blocked')->action('as_blocked')->cannot(),
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

test('a story with multiple nested stories can collate required actions', function () {
    $storyboard = Story::make()
        ->name('create something cool')
        ->can()
        ->assert(fn () => null)
        ->action('allows_creation')
        ->stories([
            Story::make('as admin')->action('as_admin')->stories([
                Story::make('if not blocked')->action('as_unblocked')->can(),
                Story::make('if blocked')->action('as_blocked')->cannot(),
            ]),
            Story::make('as customer')->action('as_customer')->stories([
                Story::make('if not blocked')->action('as_unblocked')->cannot(),
                Story::make('if blocked')->action('as_blocked')->cannot(),
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
        $actions = array_keys($story->resolveInheritedActions());

        $actual[$key] = $actions;
    }

    expect($actual)->toBe($expect);
});

test('a story cannot accept children that are not story classes', function (string $type) {
    $stories = match ($type) {
        'string' => [
            'test',
        ],
        'action' => [
            Action::make('test', fn () => true),
        ],
        'mixed' => [
            Story::make('test'),
            'test',
        ],
    };

    Story::make()->stories($stories);
})->with([
    'when given string' => 'string',
    'when given action' => 'action',
    'when given a story and a string' => 'mixed',
])->throws(InvalidStoryProvidedException::class, 'You must only provide Story classes to the stories() method.');

test('a story can fetch its children stories via collection methods and property proxies', function () {
    $storyboard = Story::make()
        ->name('parent')
        ->can()
        ->assert(fn () => null)
        ->action(fn () => null)
        ->stories([
            Story::make('child 1'),
            Story::make('child 2')->stories([
                Story::make('child 2a'),
                Story::make('child 2b'),
            ]),
        ]);

    // Register children shortcut

    $stories = $storyboard->storiesDirect;
    foreach ($stories as $story) {
        $story->register();
    }

    expect($stories)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    expect($stories->map(fn (Story $story) => $story->getName())->toArray())->toBe([
        'child 1',
        'child 2',
    ]);

    $stories = $storyboard->storiesAll;
    expect($stories)->toBeInstanceOf(Collection::class)->toHaveCount(3);
    expect($stories->map(fn (Story $story) => $story->getName())->values()->toArray())->toBe([
        'child 1',
        'child 2a',
        'child 2b',
    ]);

    // throws error
    $error = null;
    try {
        $storyboard->something_doesnt_exist;
    } catch (\Throwable $error) {
    }

    expect($error)->not()->toBeNull()
        ->and($error->getMessage())->toBe('Undefined property: BradieTilley\StoryBoard\Story::$something_doesnt_exist');
});

test('stories can append child stories in various ways', function () {
    $story = Story::make('parent')
        ->can()
        ->assert(fn () => null)
        ->action(fn () => null);

    $storyA = Story::make('story_a');
    $storyB = Story::make('story_b');
    $storyC = Story::make('story_c');
    $storyD = Story::make('story_d');
    $storyE = Story::make('story_e');
    $storyF = Story::make('story_f');
    $storyG = Story::make('story_g');
    $storyH = Story::make('story_h');

    $story->stories($storyA, $storyB, [$storyC, $storyD]);
    $story->stories([$storyE, $storyF], $storyG, [$storyH]);

    expect($story->storiesAll->keys()->toArray())->toBe([
        '[Can] parent story_a',
        '[Can] parent story_b',
        '[Can] parent story_c',
        '[Can] parent story_d',
        '[Can] parent story_e',
        '[Can] parent story_f',
        '[Can] parent story_g',
        '[Can] parent story_h',
    ]);
});

test('you can retrieve all registered actions for a story', function () {
    Action::make('action_1')->as(fn () => null);
    Action::make('action_2')->as(fn () => null);

    $story = Story::make('parent')
        ->can()
        ->assert(fn () => null)
        ->action('action_1')
        ->stories([
            $child = Story::make('child')->action('action_2'),
        ]);

    expect($actions = $child->getActions())->toHaveCount(1);

    expect($actions['action_2']->getAction()->getName())->toBe('action_2');
    expect($actions['action_2']->getArguments())->toBe([]);

    // Register all children to parent
    $stories = $story->storiesAll;
    expect($stories)->toHaveCount(1);

    /** @var Story $story */
    $story = $stories->first();

    expect($actions = $story->getActions())->toHaveCount(2);

    expect($actions['action_1']->getAction()->getName())->toBe('action_1');
    expect($actions['action_1']->getArguments())->toBe([]);

    expect($actions['action_2']->getAction()->getName())->toBe('action_2');
    expect($actions['action_2']->getArguments())->toBe([]);
});
