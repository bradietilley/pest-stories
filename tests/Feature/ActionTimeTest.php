<?php

use BradieTilley\Stories\Exceptions\TimerException;
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\story;
use Illuminate\Support\Collection;

test('an action will fail if it exceeds the specified time limit', function () {
    $action = action('do_something', function () {
        // Sleep for 0.11 seconds
        usleep(110000);
    })->timeout(0.05);

    $action->run(story());
    expect(false)->toBeTrue();
})->throws(TimerException::class);

test('an action will not fail by default', function () {
    $action = action('do_something', function () {
        // Sleep for 0.11 seconds
        usleep(110000);
    });

    $action->run(story());
    expect(true)->toBeTrue();
});

test('an action will pass if it does not exceed the specified time limit', function () {
    $action = action('do_something', function () {
        // runtime < 0.5 seconds
    })->timeout(0.5);

    $action->run(story());
    expect(true)->toBeTrue();
});

test('an action may have its timeout limit specified in milliseconds', function () {
    $action = action('do_something', fn () => null)->timeout(100, 'ms');
    expect($action->timer()->timeout)->toBe(0.1);
});

test('an action will abort mid-way through if it exceeds the timeout with abort mode enabled', function () {
    $ran = Collection::make();

    $action = action('do_something', function () use ($ran) {
        $ran[] = 'before';

        // Sleep for 1.1 second
        usleep(1100000);

        $ran[] = 'after';
    })->timeout(0.1)->abortAfterTimeout();

    try {
        $action->run(story());

        $this->fail();
    } catch (TimerException $exception) {
        //
    }

    expect($ran->toArray())->toBe([
        'before',
    ]);
})->skip(! function_exists('pcntl_alarm') || ! function_exists('pcntl_signal'));

test('an action will not abort mid-way through if it does not exceed the timeout with abort mode enabled', function () {
    $ran = Collection::make();

    $action = action('do_something', function () use ($ran) {
        $ran[] = 'before';

        // Sleep for 1 second
        usleep(10000);

        $ran[] = 'after';
    })->timeout(0.1)->abortAfterTimeout();

    $action->run(story());

    expect($ran->toArray())->toBe([
        'before',
        'after',
    ]);
});
