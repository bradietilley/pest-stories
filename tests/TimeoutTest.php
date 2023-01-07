<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Testing\Timer\TimerUpException;
use Illuminate\Support\Collection;

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
    } catch (TimerUpException $e) {
        expect($e->getTimeout())->toBe(1)
            ->and($e->getTimeRemaining())->toBe(0)
            ->and($e->getTimeTaken())->toBe(1);
    }

    expect($ran->all())->toBe([]);
});
