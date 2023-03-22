<?php

use BradieTilley\Stories\Alarm;
use BradieTilley\Stories\Exceptions\AlarmException;

test('an alarm can be created with seconds provided', function () {
    $alarm = Alarm::make(2.5, Alarm::UNIT_SECONDS);
    expect($alarm->seconds())->toBe('2.500000');

    $alarm = Alarm::make(2.5, Alarm::UNIT_SECONDS);
    expect($alarm->microseconds())->toBe(2500000);
});

test('an alarm can be created with milliseconds provided', function () {
    $alarm = Alarm::make(25, Alarm::UNIT_MILLISECONDS);
    expect($alarm->seconds())->toBe('0.025000');

    $alarm = Alarm::make(2.5, Alarm::UNIT_MILLISECONDS);
    expect($alarm->microseconds())->toBe(2500);
});

test('an alarm can be created with microseconds provided', function () {
    $alarm = Alarm::make(25613, Alarm::UNIT_MICROSECONDS);
    expect($alarm->seconds())->toBe('0.025613');

    $alarm = Alarm::make(25613, Alarm::UNIT_MICROSECONDS);
    expect($alarm->microseconds())->toBe(25613);
});

test('an alarm does nothing when limit not reached', function () {
    $alarm = Alarm::make(1, Alarm::UNIT_SECONDS);

    $alarm->start();
    usleep(200);
    $alarm->stop();

    expect(true)->toBeTrue();
});

test('an alarm throws an AlarmException when the job exceeds the time limit', function () {
    $alarm = Alarm::make(100, Alarm::UNIT_MICROSECONDS);
    $alarm->start();
    usleep(102);
    $alarm->stop();
})->throws(AlarmException::class, 'Failed to run within the specified 0.000100 seconds limit');
