<?php

use BradieTilley\StoryBoard\Exceptions\AssertionNotSpecifiedException;
use BradieTilley\StoryBoard\Exceptions\ExpectationNotSpecifiedException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Assertion;

test('a story must have at least one expectation', function () {
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
})->throws(ExpectationNotSpecifiedException::class, 'No expectation was found for the story `parent child`');

test('a story works with one expectation', function () {
    $story = Story::make()
        ->can()
        ->action(fn () => null)
        ->assert(fn () => true)
        ->name('parent')
        ->stories([
            Story::make('child'),
        ]);

    foreach ($story->allStories() as $story) {
        $story->run();
    }
})->throwsNoExceptions();

test('a story must have at least one assertion checker', function () {
    $story = Story::make()->action(fn () => null)->can()->name('parent')->stories(
        Story::make('child'),
    );

    foreach ($story->allStories() as $story) {
        $story->run();
    }
})->throws(AssertionNotSpecifiedException::class, 'No "can" assertion checker was found for the story `parent child`');

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
            Story::make()->resetExpectation()->name('child unset')->stories([
                // null: inherits from 'child unset'
                Story::make('grandchild null implicit'),
                // can: overrides resetExpectation from 'child unset'
                Story::make()->can()->name('grandchild can explicit'),
                // cannot: overrides resetExpectation from 'child unset'
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

test('assertions can be defined in various ways', function () {
    $ran = collect();

    $assertion1 = Assertion::make('assertion_1')->as(fn () => $ran[] = '1');

    Assertion::make('assertion_2')->as(fn () => $ran[] = '2');
    $assertion2 = 'assertion_2';

    $assertion3 = fn () => $ran[] = '3';

    $story = Story::make()
        ->can()
        ->assert($assertion1)
        ->assert($assertion2)
        ->assert($assertion3)
        ->action(fn () => $ran[] = 'action')
        ->boot()
        ->perform();

    expect($ran->toArray())->toBe([
        'action',
        '1',
        '2',
        '3',
    ]);
});

test('assertions can be added to a story in various ways', function () {
    $ran = collect();

    $story = Story::make()
        ->can()
        ->action(fn () => null)
        ->assert(fn () => $ran[] = 'assert')
        ->setAssertion(fn () => $ran[] = 'setAssertion')
        ->setAssertions([
            fn () => $ran[] = 'setAssertions',
        ])
        ->whenCan(fn () => $ran[] = 'whenCan')
        ->whenCannot(fn () => $ran[] = 'whenCannot')
        ->boot()
        ->perform();

    expect($ran->toArray())->toBe([
        'assert',
        'setAssertion',
        'setAssertions',
        'whenCan',
    ]);
});
