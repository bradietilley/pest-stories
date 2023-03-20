<?php

use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Helpers\StoryAliases;
use BradieTilley\Stories\Story;

test('a story can have one or more children stories added to it', function () {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');

    $ran = collect();
    $story = story('a parent story')
        ->as(fn (Story $story) => $ran[] = 'story:'.$story->getName());

    // With no children = only parent run
    $story->test()->run();

    expect($ran)->toHaveCount(1)->toArray()->toBe([
        'story:a parent story',
    ]);

    // With two children = two stories run
    $ran = collect();
    $story = story('a parent story')
        ->as(fn (Story $story) => $ran[] = 'story:'.$story->getName())
        ->stories([
            story('1'),
            story('2'),
        ]);
    $story->test()->run();

    expect($ran)->toHaveCount(2)->toArray()->toBe([
        'story:a parent story 1',
        'story:a parent story 2',
    ]);

    // With grandchildren = two children + grandchild run
    $ran = collect();
    $story = story('a parent story')
        ->as(fn (Story $story) => $ran[] = 'story:'.$story->getName())
        ->stories([
            story('child 1'),
            story('child 2')->stories([
                story('grandchild'),
            ]),
        ]);
    $story->test()->run();

    expect($ran)->toHaveCount(2)->toArray()->toBe([
        'story:a parent story child 1',
        'story:a parent story child 2 grandchild',
    ]);
});

test('story callbacks are inherited from parents', function () {
    $ran = collect();

    $stories = story('parent story')
        ->before(fn () => $ran[] = 'parent 1')
        ->stories([
            story('child story 1')->before(fn () => $ran[] = 'child 1'),
            story('child story 2')->before(fn () => $ran[] = 'child 2')->stories([
                story('grandchild story 1')->before(fn () => $ran[] = 'grandchild 1'),
                story('grandchild story 2')->before(fn () => $ran[] = 'grandchild 2'),
            ]),
        ])
        ->flattenStories();

    foreach ($stories as $story) {
        $story->process();
    }

    expect($ran->toArray())->toBe([
        // Child 1
        'parent 1',
        'child 1',
        // Child 2
        'parent 1',
        'child 2',
        'grandchild 1',
        // Child 3
        'parent 1',
        'child 2',
        'grandchild 2',
    ]);
});