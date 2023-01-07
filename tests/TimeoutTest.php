<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Testing\Timer\TimerUpException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\ExpectationFailedException;

test('a story with a timeout will pass if it does not reach the timeout', function () {
    $ran = Collection::make();

    Story::make('timed test')
        ->can()
        ->task(function () use (&$ran) {
            $ran[] = 'test';
        })
        ->check(fn () => null)
        ->timeout(10)
        ->run();

    expect($ran->all())->toBe([
        'test',
    ]);
});

test('a story with a timeout will fail if it reaches the timeout', function () {
    $ran = Collection::make();

    $story = Story::make('timed test')
        ->can()
        ->task(function () use ($ran) {
            sleep(2);

            $ran[] = 'test';
        })
        ->check(fn () => null)
        ->timeout(1);

    try {
        $story->run();

        $this->fail('Story should have timed out');
    } catch (ExpectationFailedException $e) {
        expect($e->getMessage())
            ->toStartWith('Failed asserting that this task would complete in less than 1 second.');
    }

    expect($ran->all())->toBe([]);
});
