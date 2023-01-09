<?php

use BradieTilley\StoryBoard\Exceptions\AssertionCheckerNotFoundException;
use BradieTilley\StoryBoard\Exceptions\AssertionNotFoundException;
use BradieTilley\StoryBoard\Story;

test('a story must have at least one assertion', function () {
    $story = Story::make()
        ->action(fn () => null)
        ->assert(fn () => true)
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


test('you may create a story with an assertion and unset the assertion for a child story', function () {
    $story = Story::make()
        ->name('parent')
        ->can()
        ->stories([
            // can: inherits from 'parent'
            Story::make('child can implicit'),
            // can: overrwides from 'parent' (no affect really)
            Story::make()->can()->name('child can explicit'),
            // cannot: overrwides from 'parent'
            Story::make()->cannot()->name('child cannot explicit'),
            // null: overrwides from 'parent'
            Story::make()->noAssertion()->name('child unset')->stories([
                // null: inherits from 'child unset'
                Story::make('grandchild null implicit'),
                // can: overrides noAssertion from 'child unset'
                Story::make()->can()->name('grandchild can explicit'),
                // cannot: overrides noAssertion from 'child unset'
                Story::make()->cannot()->name('grandchild cannot explicit'),
            ]),
        ]);

    $actual = $story->storiesAll->keys()->toArray();

    $expect = [
        '[Can] parent child can implicit',
        '[Can] parent child can explicit',
        '[Cannot] parent child cannot explicit',
        // This should have no [Can]/[Cannot] as it was reset to have no assertion
        'parent child unset grandchild null implicit',
        '[Can] parent child unset grandchild can explicit',
        '[Cannot] parent child unset grandchild cannot explicit',
    ];

    expect($actual)->toBe($expect);
});