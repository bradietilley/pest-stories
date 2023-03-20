<?php

use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Helpers\StoryAliases;
use BradieTilley\Stories\Story;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\SkippedWithMessageException;

test('a story can mark the test as todo', function () {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');

    $story = story('todo story');

    $call = $story->register();
    expect($call->todo)->toBeFalse();

    $story->todo();

    $call = $story->register();
    expect($call->todo)->toBeTrue();
});

test('a story can mark the test as skipped', function () {
    $story = story('skipped story');
    $story->process();

    try {
        $story->skipped('skipped because');
        $story->process();

        $this->fail('Expected to have thrown SkippedWithMessageException');
    } catch (Exception $exception) {
        expect($exception)->toBeInstanceOf(SkippedWithMessageException::class);
    }
});

test('a story can mark the test as incomplete', function () {
    $story = story('incomplete story');
    $story->process();

    try {
        $story->incomplete('incomplete because');
        $story->process();

        $this->fail('Expected to have thrown IncompleteTestError');
    } catch (Exception $exception) {
        expect($exception)->toBeInstanceOf(IncompleteTestError::class);
    }
});

test('a story can mark the test as skipped or incomplete at a parent level', function () {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');

    $story = story('something not working')
        ->stories([
            story('a')->incomplete('not complete 1'),
            story('b')->incomplete()->stories([
                story('not complete 2a'),
                story('not complete 2b'),
                story('not complete 2c')->stories([
                    story('i'),
                ]),
            ]),
        ]);

    $call = $story->register();

    // Sanity check
    expect($call->dataset)->toBeArray()->toHaveCount(4)->toHaveKeys([
        'a',
        'b not complete 2a',
        'b not complete 2b',
        'b not complete 2c i',
    ]);

    // All 4 should be incomplete

    foreach ($call->dataset as $args) {
        $story = $args[0];
        /** @var Story $story */
        try {
            $story->process();

            $this->fail('Should be incomplete');
        } catch (IncompleteTestError $exception) {
            expect($exception)->toBeInstanceOf(IncompleteTestError::class);
        }
    }
});
