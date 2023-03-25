<?php

use function BradieTilley\Stories\Helpers\story;

test('a standard one-level story is named appropriately', function () {
    $story = story('do something great');

    expect($story->getTestName())->toBe('do something great');
});

test('a complex multi-level story is named appropriately', function () {
    $story = story('do something great')
        ->stories([
            story('with ease')->stories([
                story('and style'),
                story('and fun'),
            ]),
        ]);

    $names = $story->flattenStories()
        ->map->getTestName()
        ->toArray();

    expect($names)->toBe([
        'do something great with ease and style',
        'do something great with ease and fun',
    ]);
});

test('stories can have pre-defined variables appended to the name for ease of varying story names', function () {
    $story = story('great things start with')
        ->action(fn (string $name) => null)
        ->set('name', 'pest')
        ->appends('name')
        ->stories([
            story(),
            story()->set('name', 'pestphp'),
            story()->set('name', 'pest stories'),
        ]);

    $names = $story->flattenStories()
        ->map->getTestName()
        ->toArray();

    expect($names)->toBe([
        'great things start with name: pest',
        'great things start with name: pestphp',
        'great things start with name: pest stories',
    ]);
});
