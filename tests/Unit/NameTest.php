<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Config;
use BradieTilley\StoryBoard\Traits\HasName;
use Illuminate\Support\Collection;

test('a story can be given a name using different shortcuts', function () {
    $story = Story::make()
        ->can()
        ->assert(fn () => null)
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
    $d = 1;
    $story = Story::make('my name');

    expect($story->getName())->toBe('my name');
});

test('a storyboard will not prefix its story names with the parent name when dataset mode is enabled', function () {
    $story = Story::make('parent')
        ->can()
        ->assert(fn () => null)
        ->stories([
            Story::make('child 1'),
            Story::make('child 2'),
            Story::make('child 3'),
        ]);

    Config::disableDatasets();

    expect($story->storiesAll->map(fn (Story $story) => $story->getTestName())->values()->toArray())->toBe([
        '[Can] parent child 1',
        '[Can] parent child 2',
        '[Can] parent child 3',
    ]);

    Config::enableDatasets();

    expect($story->storiesAll->map(fn (Story $story) => $story->getTestName())->values()->toArray())->toBe([
        '[Can] child 1',
        '[Can] child 2',
        '[Can] child 3',
    ]);

    // Reset
    Config::disableDatasets();
});

test('can inherit name from parents', function () {
    $story = Story::make('parent')
        ->can()
        ->assert(fn () => null)
        ->stories([
            $child1 = Story::make('child 1'),
            Story::make('child 2')->stories([
                $grandchild1 = Story::make('grandchild 1'),
            ]),
            Story::make()->stories([
                $grandchild2 = Story::make('grandchild 2'),
            ]),
        ]);

    // Disable datasets
    Config::disableDatasets();

    $story->storiesAll;

    // Names should be what we're expecting
    $name = Collection::make();
    $name[] = $child1->getFullName();
    $name[] = $grandchild1->getFullName();
    $name[] = $grandchild2->getFullName();

    expect($name->toArray())->toBe([
        'parent child 1',
        'parent child 2 grandchild 1',
        'parent grandchild 2',
    ]);

    // Try inherit it again
    $child1->inheritName();
    $grandchild1->inheritName();
    $grandchild2->inheritName();

    // Names should be what we're expecting
    $name = Collection::make();
    $name[] = $child1->getFullName();
    $name[] = $grandchild1->getFullName();
    $name[] = $grandchild2->getFullName();

    expect($name->toArray())->toBe([
        'parent child 1',
        'parent child 2 grandchild 1',
        'parent grandchild 2',
    ]);

    // Enable datasets (should change the name)
    Config::enableDatasets();

    // Try inherit it again
    $child1->inheritName();
    $grandchild1->inheritName();
    $grandchild2->inheritName();

    // Names should be what we're expecting
    $name = Collection::make();
    $name[] = $child1->getFullName();
    $name[] = $grandchild1->getFullName();
    $name[] = $grandchild2->getFullName();

    expect($name->toArray())->toBe([
        'child 1',
        'child 2 grandchild 1',
        'grandchild 2',
    ]);

    // Disable datasets for remainder of tests
    Config::disableDatasets();
});

test('an object with HasName but without WithInheritance will safely not inherit', function () {
    $class = new class()
    {
        use HasName;
    };
    $class->inheritName();

    // no error
    expect(true)->toBeTrue();
});
