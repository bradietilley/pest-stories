<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;

test('a story can be given a name using different shortcuts', function () {
    $story = StoryBoard::make()
        ->can()
        ->task(fn () => null)
        ->check(fn () => null)
        ->stories([
            Story::make('do something basic'),
            Story::make()->view('App\Models\Brand'),
            Story::make()->create('App\Models\Product'),
            Story::make()->update('App\Models\Invoice'),
            Story::make()->delete('App\Models\User'),
            Story::make()->restore('App\Models\Category'),
        ])
        ->storiesAll
        ->keys();

    expect($story->toArray())->toBe([
        '[Can] do something basic',
        '[Can] view a Brand',
        '[Can] create a Product',
        '[Can] update a Invoice',
        '[Can] delete a User',
        '[Can] restore a Category',
    ]);
});

test('a story can be named during the make static constructor', function () {
    $story = Story::make('my name');

    expect($story->getName())->toBe('my name');
});

test('a storyboard will not prefix its story names with the parent name when dataset mode is enabled', function () {
    $story = StoryBoard::make('parent')
        ->can()
        ->stories([
            Story::make('child 1'),
            Story::make('child 2'),
            Story::make('child 3'),
        ]);

    StoryBoard::disableDatasets();

    expect(array_keys($story->allStories()))->toBe([
        '[Can] parent child 1',
        '[Can] parent child 2',
        '[Can] parent child 3',
    ]);

    StoryBoard::enableDatasets();

    expect(array_keys($story->allStories()))->toBe([
        '[Can] child 1',
        '[Can] child 2',
        '[Can] child 3',
    ]);

    // Reset
    StoryBoard::disableDatasets();
});
