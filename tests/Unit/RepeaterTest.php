<?php

use BradieTilley\Stories\Repeater;

test('a repeater can increment until its maximum value', function () {
    $repeater = Repeater::make()->setMax(2);

    // More doesn't increment
    expect($repeater->more())->toBeTrue();
    expect($repeater->more())->toBeTrue();
    expect($repeater->more())->toBeTrue();
    expect($repeater->more())->toBeTrue();

    // Increment once = still more
    expect($repeater->increment());
    expect($repeater->more())->toBeTrue();

    // Increment again = no more
    expect($repeater->increment());
    expect($repeater->more())->toBeFalse();
});

test('a repeater more and increment foreach loop yields the expected number of iterations', function () {
    $repeater = Repeater::make()->setMax(10);

    $all = 0;

    while ($repeater->more()) {
        $all++;

        $repeater->increment();
    }

    expect($all)->toBe(10);
});

test('a repeater with one iteration will run once', function () {
    $repeater = Repeater::make();

    expect($repeater->max())->toBe(1);
    expect($repeater->run())->toBe(0);

    $all = 0;

    while ($repeater->more()) {
        $all++;

        $repeater->increment();
    }

    expect($all)->toBe(1);
});

test('a repeater can be reset', function () {
    $repeater = Repeater::make()->setMax(5);

    expect($repeater->max())->toBe(5);
    expect($repeater->run())->toBe(0);

    $repeater->increment();
    expect($repeater->max())->toBe(5);
    expect($repeater->run())->toBe(1);

    $repeater->increment();
    expect($repeater->max())->toBe(5);
    expect($repeater->run())->toBe(2);

    $repeater->reset();
    expect($repeater->max())->toBe(5);
    expect($repeater->run())->toBe(0);
});

test('a repeater can be stopped', function () {
    $repeater = Repeater::make()->setMax(5);

    expect($repeater->max())->toBe(5);
    expect($repeater->run())->toBe(0);

    $repeater->increment();
    expect($repeater->max())->toBe(5);
    expect($repeater->run())->toBe(1);

    $repeater->stop();
    expect($repeater->max())->toBe(5);
    expect($repeater->run())->toBe(5);
    expect($repeater->more())->toBeFalse();
});
