<?php

use BradieTilley\StoryBoard\Enums\StoryStatus;
use BradieTilley\StoryBoard\Story;
use PHPUnit\Framework\TestCase;

function createStory(): Story
{
    return Story::make('test')
        ->can()
        ->assert(fn () => null)
        ->action(fn () => null);
}

test('a story status is pending until it registers', function () {
    $story = createStory();
    expect($story->getStatus())->toBe(StoryStatus::PENDING);

    $story->register();
    expect($story->getStatus())->toBe(StoryStatus::RUNNING);
});

test('a story status is failure when an exception is thrown', function () {
    $story = createStory()->action(fn () => throw new InvalidArgumentException('Test'));
    expect($story->getStatus())->toBe(StoryStatus::PENDING);

    try {
        $story->run();
    } catch (Throwable $e) {
    }

    expect($story->getStatus())->toBe(StoryStatus::FAILURE);
});

test('a story status is skipped when the story is skipped', function () {
    $story = createStory()->skipped('test');
    expect($story->getStatus())->toBe(StoryStatus::PENDING);

    try {
        /** @var TestCase $this */
        $test = $this;

        $story->setTest($test)->run();
    } catch (Throwable $e) {
    }

    expect($story->getStatus())->toBe(StoryStatus::SKIPPED);
});

test('a story status is incomplete when the story is incomplete', function () {
    $story = createStory()->incomplete('test');
    expect($story->getStatus())->toBe(StoryStatus::PENDING);

    try {
        /** @var TestCase $this */
        $test = $this;

        $story->setTest($test)->run();
    } catch (Throwable $e) {
    }

    expect($story->getStatus())->toBe(StoryStatus::INCOMPLETE);
});

test('a story status is risky when the story is risky', function () {
    $story = createStory()->risky('test');
    expect($story->getStatus())->toBe(StoryStatus::PENDING);

    try {
        /** @var TestCase $this */
        $test = $this;

        $story->setTest($test)->run();
    } catch (Throwable $e) {
    }

    expect($story->getStatus())->toBe(StoryStatus::RISKY);
});

test('a story status is success when the story completes with no exceptions', function () {
    $story = createStory();
    expect($story->getStatus())->toBe(StoryStatus::PENDING);

    try {
        /** @var TestCase $this */
        $test = $this;

        $story->setTest($test)->run();
    } catch (Throwable $e) {
    }

    expect($story->getStatus())->toBe(StoryStatus::SUCCESS);
});