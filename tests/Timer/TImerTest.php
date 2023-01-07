<?php

use BradieTilley\StoryBoard\Testing\Timer\TimerUnit;

test('timer unit can convert between units', function () {
    /**
     * Second -> Second
     * Second -> Millisecond
     * Second -> Microsecond
     * 
     * Millisecond -> Second
     * Millisecond -> Millisecond
     * Millisecond -> Microsecond
     * 
     * Microsecond -> Second
     * Microsecond -> Millisecond
     * Microsecond -> Microsecond
     */
    expect(TimerUnit::SECOND->toUnit(1, TimerUnit::SECOND))->toBe(1);
    expect(TimerUnit::SECOND->toUnit(2, TimerUnit::SECOND))->toBe(2);

    expect(TimerUnit::SECOND->toUnit(1, TimerUnit::MILLISECOND))->toBe(1000);
    expect(TimerUnit::SECOND->toUnit(2, TimerUnit::MILLISECOND))->toBe(2000);

    expect(TimerUnit::SECOND->toUnit(1, TimerUnit::MICROSECOND))->toBe(1000000);
    expect(TimerUnit::SECOND->toUnit(2, TimerUnit::MICROSECOND))->toBe(2000000);

    expect(TimerUnit::MILLISECOND->toUnit(1000, TimerUnit::SECOND))->toBe(1.0);
    expect(TimerUnit::MILLISECOND->toUnit(2000, TimerUnit::SECOND))->toBe(2.0);

    expect(TimerUnit::MILLISECOND->toUnit(1000, TimerUnit::MILLISECOND))->toBe(1000);
    expect(TimerUnit::MILLISECOND->toUnit(2000, TimerUnit::MILLISECOND))->toBe(2000);

    expect(TimerUnit::MILLISECOND->toUnit(1000, TimerUnit::MICROSECOND))->toBe(1000000);
    expect(TimerUnit::MILLISECOND->toUnit(2000, TimerUnit::MICROSECOND))->toBe(2000000);

    expect(TimerUnit::MICROSECOND->toUnit(1000000, TimerUnit::SECOND))->toBe(1.0);
    expect(TimerUnit::MICROSECOND->toUnit(2000000, TimerUnit::SECOND))->toBe(2.0);

    expect(TimerUnit::MICROSECOND->toUnit(1000000, TimerUnit::MILLISECOND))->toBe(1000.0);
    expect(TimerUnit::MICROSECOND->toUnit(2000000, TimerUnit::MILLISECOND))->toBe(2000.0);

    expect(TimerUnit::MICROSECOND->toUnit(1000000, TimerUnit::MICROSECOND))->toBe(1000000);
    expect(TimerUnit::MICROSECOND->toUnit(2000000, TimerUnit::MICROSECOND))->toBe(2000000);

    /**
     * Additional tests involving floats
     */

    expect(TimerUnit::MILLISECOND->toUnit(1, TimerUnit::SECOND))->toBe(0.001);
    expect(TimerUnit::MILLISECOND->toUnit(2, TimerUnit::SECOND))->toBe(0.002);

    expect(TimerUnit::MICROSECOND->toUnit(1, TimerUnit::MILLISECOND))->toBe(0.001);
    expect(TimerUnit::MICROSECOND->toUnit(2, TimerUnit::MILLISECOND))->toBe(0.002);

    expect(TimerUnit::MICROSECOND->toUnit(1, TimerUnit::SECOND))->toBe(0.000001);
    expect(TimerUnit::MICROSECOND->toUnit(2, TimerUnit::SECOND))->toBe(0.000002);
});

test('timer unit can format the label for various times', function () {
    expect(TimerUnit::SECOND->format(1))->toBe('1 second')
        ->and(TimerUnit::SECOND->format(2))->toBe('2 seconds')
        ->and(TimerUnit::SECOND->format(2.123456))->toBe('2.123456 seconds')
        ->and(TimerUnit::MILLISECOND->format(1))->toBe('1 millisecond')
        ->and(TimerUnit::MILLISECOND->format(2))->toBe('2 milliseconds')
        ->and(TimerUnit::MILLISECOND->format(2.543))->toBe('2.543 milliseconds')
        ->and(TimerUnit::MICROSECOND->format(1))->toBe('1 microsecond')
        ->and(TimerUnit::MICROSECOND->format(2))->toBe('2 microseconds')
        ->and(TimerUnit::MICROSECOND->format(2.1))->toBe('2.1 microseconds');
});
