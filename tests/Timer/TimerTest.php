<?php

use BradieTilley\StoryBoard\Testing\Timer\Timer;
use BradieTilley\StoryBoard\Testing\Timer\TimerUnit;
use BradieTilley\StoryBoard\Testing\Timer\TimerUpException;
use Illuminate\Support\Collection;

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

class ExampleExtendedTimerUpException extends TimerUpException
{
}

test('timer will rethrow a TimerUpException if thrown within the timedOut callback', function () {
    $timer = Timer::make(fn () => usleep(5001))
        ->timeout(5000, TimerUnit::MICROSECOND)
        ->timedout(function (Timer $timer, $e) {
            throw new ExampleExtendedTimerUpException($timer);
        });

    $timer->run();
})->throws(ExampleExtendedTimerUpException::class);

test('timer can rethrow throwables if thrown within the primary callback', function (bool $rethrow) {
    $timer = Timer::make(
        callback: function () {
            throw new InvalidArgumentException('Test invalid arg');
        },
        rethrow: $rethrow,
        timeout: 1
    );

    
    try {
        $timer->run();
        
        if ($rethrow) {
            $this->fail('rethrowing means the invalid arg exception should have been thrown');
        } else {
            expect($timer->getException())->not()->toBeNull();
        }
    } catch (InvalidArgumentException $e) {
        expect($timer->getException())->not()->toBeNull();

        if (! $rethrow) {
            $this->fail('not rethrowing = exception should not be thrown');
        }

        expect($e->getMessage())->toBe('Test invalid arg');
    }
})->with([
    'rethrow enabled' => true,
    'rethrow disabled' => false,
]);

test('a timer that fails calls the errored and after callback', function () {
    $ran = Collection::make();

    Timer::make(
        function () use ($ran) {
            $ran[] = 'callback';

            throw new InvalidArgumentException('test error');
        },
        rethrow: false,
    )
        ->finished(fn () => $ran[] = 'finished')
        ->errored(fn () => $ran[] = 'errored')
        ->timedout(fn () => $ran[] = 'timedout')
        ->after(fn () => $ran[] = 'after')
        ->run();

    expect($ran->toArray())->toBe([
        'callback',
        'errored',
        'after',
    ]);
});

test('a timer that times out calls the timedout and after callback', function () {
    $ran = Collection::make();

    Timer::make(
        function () use ($ran) {
            $ran[] = 'callback';
            usleep(1001);
        },
        rethrow: false,
    )
        ->timeout(0.001)
        ->finished(fn () => $ran[] = 'finished')
        ->errored(fn () => $ran[] = 'errored')
        ->timedout(fn () => $ran[] = 'timedout')
        ->after(fn () => $ran[] = 'after')
        ->run();

    expect($ran->toArray())->toBe([
        'callback',
        'timedout',
        'after',
    ]);
});

test('a timer that passes calls the finished callback', function () {
    $ran = Collection::make();

    Timer::make(
        function () use ($ran) {
            $ran[] = 'callback';
        },
        rethrow: false,
    )
        ->timeout(1)
        ->finished(fn () => $ran[] = 'finished')
        ->errored(fn () => $ran[] = 'errored')
        ->timedout(fn () => $ran[] = 'timedout')
        ->after(fn () => $ran[] = 'after')
        ->run();

    expect($ran->toArray())->toBe([
        'callback',
        'finished',
        'after',
    ]);
});
