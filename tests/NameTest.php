<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;

test('a story can be given a name using different shortcuts', function () {
    $story = StoryBoard::make()
        ->can()
        ->task(fn () => null)
        ->check(fn () => null)
        ->stories([
            Story::make()->name('do something basic'),
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