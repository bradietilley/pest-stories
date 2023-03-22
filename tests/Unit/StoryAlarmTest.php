<?php

use BradieTilley\Stories\Alarm;
use BradieTilley\Stories\Exceptions\AlarmException;
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\assertion;
use function BradieTilley\Stories\Helpers\story;

test('a story with an alarm will fail if it has an alarm and reaches the limit', function () {
    action('do_something')->as(fn () => usleep(100));

    story('test')->action('do_something')->timeout(99)->process();
})->throws(AlarmException::class, 'Failed to run within the specified 0.000099 seconds limit');

test('a story with an alarm will not fail if it has an alarm but does not reach the limit', function () {
    action('do_something')->as(fn () => usleep(100));

    story('test')->action('do_something')->timeout(1, Alarm::UNIT_SECONDS)->process();
    expect(true)->toBeTrue();
});

test('a story with an alarm will not fail if it does not have an alarm', function () {
    action('do_something')->as(fn () => usleep(100));

    story('test')->action('do_something')->process();
    expect(true)->toBeTrue();
});

test('an action used by a story will fail if has an alarm and reaches the limit', function () {
    action('do_something')->as(fn () => usleep(100))->timeout(99);

    story('test')->action('do_something')->process();
})->throws(AlarmException::class, 'Failed to run within the specified 0.000099 seconds limit');

test('an assertion used by a story will fail if has an alarm and reaches the limit', function () {
    assertion('do_something')->as(fn () => usleep(100))->timeout(99);

    story('test')->assertion('do_something')->process();
})->throws(AlarmException::class, 'Failed to run within the specified 0.000099 seconds limit');
