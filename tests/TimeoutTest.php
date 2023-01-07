<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Testing\Timer\TimerUnit;
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

test('a story with a timeout will fail if it reaches the timeout (seconds; via alarm)', function () {
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

    /**
     * Story was killed by the pcntl signal; nothing was appended to $ran
     */
    expect($ran->all())->toBe([]);
});

test('a story with a timeout will fail if it reaches the timeout (milliseconds; via microtime)', function () {
    $ran = Collection::make();

    $story = Story::make('timed test')
        ->can()
        ->task(function () use ($ran) {
            // 0.01s = 10ms = 10,000 microseconds, so usleep for 10,001
            usleep(10001);

            $ran[] = 'test';
        })
        ->check(fn () => null)
        ->timeout(0.01);

    try {
        $story->run();

        $this->fail('Story should have timed out');
    } catch (ExpectationFailedException $e) {
        expect($e->getMessage())
            ->toStartWith('Failed asserting that this task would complete in less than 0.01 seconds.');
    }

    /**
     * Story is not killed by the pcntl signal; so 'test' is appended to $ran.
     * 
     * 0.1 seconds is rounded to 1 second. If story takes longer than 1 second, it would be killed.
     */
    expect($ran->all())->toBe([
        'test',
    ]);
});

test('a story with a timeout will fail if it reaches the timeout (microseconds; via microtime)', function () {
    $ran = Collection::make();

    $story = Story::make('timed test')
        ->can()
        ->task(function () use ($ran) {
            // 0.00001s = 10 microseconds, so usleep for 11
            usleep(11);

            $ran[] = 'test';
        })
        ->check(fn () => null)
        ->timeout(0.00001);

    try {
        $story->run();

        $this->fail('Story should have timed out');
    } catch (ExpectationFailedException $e) {
        expect($e->getMessage())
            ->toStartWith('Failed asserting that this task would complete in less than 0.00001 seconds.');
    }

    /**
     * Story is not killed by the pcntl signal; so 'test' is appended to $ran.
     * 
     * 0.1 seconds is rounded to 1 second. If story takes longer than 1 second, it would be killed.
     */
    expect($ran->all())->toBe([
        'test',
    ]);
});
