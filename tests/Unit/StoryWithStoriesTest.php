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
    $story->register()->run();

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
    $story->register()->run();

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
    $story->register()->run();

    expect($ran)->toHaveCount(2)->toArray()->toBe([
        'story:a parent story child 1',
        'story:a parent story child 2 grandchild',
    ]);
});
