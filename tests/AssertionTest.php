<?php

use BradieTilley\StoryBoard\Exceptions\AssertionCheckerNotFoundException;
use BradieTilley\StoryBoard\Exceptions\AssertionNotFoundException;
use BradieTilley\StoryBoard\Story;

test('a story must have at least one assertion', function () {
    $story = Story::make()
        ->action(fn () => null)
        ->check(fn () => true)
        ->name('parent')
        ->stories([
            Story::make('child'),
        ]);

    foreach ($story->allStories() as $story) {
        $story->run();
    }
})->throws(AssertionNotFoundException::class, 'No assertion was found for the story `parent child`');

test('a story must have at least one assertion checker', function () {
    $story = Story::make()->action(fn () => null)->can()->name('parent')->stories(
        Story::make('child'),
    );

    foreach ($story->allStories() as $story) {
        $story->run();
    }
})->throws(AssertionCheckerNotFoundException::class, 'No "can" assertion checker was found for the story `parent child`');
