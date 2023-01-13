<?php

use BradieTilley\StoryBoard\Story;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\RiskyTestError;
use PHPUnit\Framework\SkippedTestError;
use Tests\TestCase;

test('a story can be marked as incomplete', function (string $message) {
    $story = Story::make('parent')
        ->incomplete($message)
        ->stories([
            Story::make('child'),
        ])
        ->storiesAll
        ->first();

    try {
        /** @var TestCase $this */
        $test = $this;
        $story->setTest($test)->run();

        $this->fail();
    } catch (IncompleteTestError $e) {
        expect($e->getMessage())->toBe($message);
    }
})->with([
    'no message' => '',
    'a message' => 'this is a wip',
]);

test('a story can be marked as skipped', function (string $message) {
    $story = Story::make('parent')
        ->skipped($message)
        ->stories([
            Story::make('child'),
        ])
        ->storiesAll
        ->first();

    try {
        /** @var TestCase $this */
        $test = $this;
        $story->setTest($test)->run();

        $this->fail();
    } catch (SkippedTestError $e) {
        expect($e->getMessage())->toBe($message);
    }
})->with([
    'no message' => '',
    'a message' => 'will work on later',
]);

test('a story can be marked as risky', function () {
    $story = Story::make('parent')
        ->risky()
        ->stories([
            Story::make('child'),
        ])
        ->storiesAll
        ->first();

    try {
        /** @var TestCase $this */
        $test = $this;
        $story->setTest($test)->run();

        $this->fail();
    } catch (RiskyTestError $e) {
        expect($e->getMessage())->toBe('This story is risky');
    }
});

test('you can fetch the testcase shortcuts from a story', function () {
    $story = Story::make('parent')
        ->risky()
        ->incomplete('a')
        ->skipped('b')
        ->stories([
            Story::make('child'),
        ])
        ->storiesAll
        ->first();

    expect($story->getTestCaseShortcuts())->toBe([
        'risky' => true,
        'incomplete' => 'a',
        'skipped' => 'b',
    ]);
});
