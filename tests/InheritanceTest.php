<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;

test('inheritance can retrieve parent', function () {
    $parent = StoryBoard::make('parent')
        ->stories([
            $child = Story::make('child'),
            $another = Story::make('another')->stories([
                $grandchild = Story::make('grandchild'),
            ]),
        ]);

    expect($grandchild->getParent())->toBe($another)
        ->and($another->getParent())->toBe($parent)
        ->and($child->getParent())->toBe($parent)
        ->and($parent->getParent())->toBe(null);
});

test('inheritance can check existence parent', function () {
    $parent = StoryBoard::make('parent')
        ->stories([
            $child = Story::make('child'),
            $another = Story::make('another')->stories([
                $grandchild = Story::make('grandchild'),
            ]),
        ]);

    expect($grandchild->hasParent())->toBeTrue()
        ->and($another->hasParent())->toBeTrue()
        ->and($child->hasParent())->toBeTrue()
        ->and($parent->hasParent())->toBeFalse();
});

