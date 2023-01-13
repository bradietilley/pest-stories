<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Testing\Timer\Timer;
use BradieTilley\StoryBoard\Testing\Timer\TimerUnit;
use Illuminate\Support\Collection;
use PHPUnit\Framework\ExpectationFailedException;

beforeEach(function () {
    if (! Timer::environmentSupportsPcntlAlarm()) {
        return $this->markTestSkipped('Environment does not support pcntl_alarm');
    }
});

test('a story can be given a timeout and it will yield the correct timeout', function () {
    $tests = [
        // Seconds
        [
            'timeout' => 1,
            'unit' => TimerUnit::SECOND,
            'micro' => 1000000,
            'pcntl' => 1,
        ],
        [
            'timeout' => 1.01,
            'unit' => TimerUnit::SECOND,
            'micro' => 1010000,
            'pcntl' => 2,
        ],
        [
            'timeout' => 1.999,
            'unit' => TimerUnit::SECOND,
            'micro' => 1999000,
            'pcntl' => 2,
        ],
        [
            'timeout' => 0.1,
            'unit' => TimerUnit::SECOND,
            'micro' => 100000,
            'pcntl' => 1,
        ],
        // Milliseconds
        [
            'timeout' => 1,
            'unit' => TimerUnit::MILLISECOND,
            'micro' => 1000,
            'pcntl' => 1,
        ],
        [
            'timeout' => 10.5,
            'unit' => TimerUnit::MILLISECOND,
            'micro' => 10500,
            'pcntl' => 1,
        ],
        [
            'timeout' => 999,
            'unit' => TimerUnit::MILLISECOND,
            'micro' => 999000,
            'pcntl' => 1,
        ],
        [
            'timeout' => 1000,
            'unit' => TimerUnit::MILLISECOND,
            'micro' => 1000000,
            'pcntl' => 1,
        ],
        [
            'timeout' => 1001,
            'unit' => TimerUnit::MILLISECOND,
            'micro' => 1001000,
            'pcntl' => 2,
        ],
        // Microseconds
        [
            'timeout' => 1,
            'unit' => TimerUnit::MICROSECOND,
            'micro' => 1,
            'pcntl' => 1,
        ],
        [
            'timeout' => 10,
            'unit' => TimerUnit::MICROSECOND,
            'micro' => 10,
            'pcntl' => 1,
        ],
        [
            'timeout' => 999,
            'unit' => TimerUnit::MICROSECOND,
            'micro' => 999,
            'pcntl' => 1,
        ],
        [
            'timeout' => 200000,
            'unit' => TimerUnit::MICROSECOND,
            'micro' => 200000,
            'pcntl' => 1,
        ],
        [
            'timeout' => 1000001,
            'unit' => TimerUnit::MICROSECOND,
            'micro' => 1000001,
            'pcntl' => 2,
        ],
    ];

    foreach ($tests as $test) {
        $story = Story::make('timeout test')->timeout($test['timeout'], $test['unit']);
        expect($story->getTimeoutMicroseconds())->toBe($test['micro']);

        $timer = $story->createTimer(fn () => null);
        expect($timer->getAlarmTimeout())->toBe($test['pcntl']);
    }
});

test('a story with a timeout will pass if it does not reach the timeout', function () {
    $ran = Collection::make();

    Story::make('timed test')
        ->can()
        ->action(function () use (&$ran) {
            $ran[] = 'test';
        })
        ->assert(fn () => null)
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
        ->action(function () use ($ran) {
            sleep(2);

            $ran[] = 'test';
        })
        ->assert(fn () => null)
        ->timeout(1);

    try {
        $story->run();

        $this->fail('Story should have timed out');
    } catch (ExpectationFailedException $e) {
        expect($e->getMessage())
            ->toStartWith('Failed asserting that this story would complete in less than 1 second.');
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
        ->action(function () use ($ran) {
            // 0.01s = 10ms = 10,000 microseconds, so usleep for 10,001
            usleep(10001);

            $ran[] = 'test';
        })
        ->assert(fn () => null)
        ->timeout(0.01);

    try {
        $story->run();

        $this->fail('Story should have timed out');
    } catch (ExpectationFailedException $e) {
        expect($e->getMessage())
            ->toStartWith('Failed asserting that this story would complete in less than 0.01 seconds.');
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
        ->action(function () use ($ran) {
            // 0.00001s = 10 microseconds, so usleep for 11
            usleep(11);

            $ran[] = 'test';
        })
        ->assert(fn () => null)
        ->timeout(0.00001);

    try {
        $story->run();

        $this->fail('Story should have timed out');
    } catch (ExpectationFailedException $e) {
        expect($e->getMessage())
            ->toStartWith('Failed asserting that this story would complete in less than 0.00001 seconds.');
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

test('a timeout can be inherited from parents with no timeout override on children', function () {
    $ran = Collection::make();

    $story = Story::make('parent')
        ->can()
        ->assert(fn () => null)
        ->action(function (Story $story) use ($ran) {
            usleep(20002);

            $ran[] = $story->getName();
        })
        ->timeout(0.001)
        ->stories([
            $child1 = Story::make('child 1 inherit 0.001 timeout'),
            $child2 = Story::make('child 2 override 0.002 timeout')->timeout(0.002),
            $child3 = Story::make('child 3 override without timeout')->noTimeout(),
            Story::make('child 4 override no timeout')->noTimeout()->stories([
                $child4 = Story::make('child 4a'),
            ]),
        ]);

    $story->storiesAll;

    try {
        $child1->run();
        $this->fail('Story should have timed out');
    } catch (ExpectationFailedException $e) {
        expect($e->getMessage())
            ->toStartWith('Failed asserting that this story would complete in less than 0.001 seconds.');
    }

    try {
        $child2->run();
        $this->fail('Story should have timed out');
    } catch (ExpectationFailedException $e) {
        expect($e->getMessage())
            ->toStartWith('Failed asserting that this story would complete in less than 0.002 seconds.');
    }

    $child3->run();
    $child4->run();

    expect($ran->toArray())->toBe([
        'child 1 inherit 0.001 timeout',
        'child 2 override 0.002 timeout',
        'child 3 override without timeout',
        'child 4a',
    ]);
});

test('a story can expose the timer used for asserting time', function () {
    $story = Story::make('timed test')
        ->can()
        ->action(fn () => null)
        ->assert(fn () => null)
        ->timeout(0.01);

    expect($story->getTimer())->toBeNull();

    $start = microtime(true) * 1000000;
    $story->run();
    $end = microtime(true) * 1000000;

    expect($timer = $story->getTimer())->toBeInstanceOf(Timer::class);

    expect($timer)
        ->getTimeout()->toBe(10000)
        ->getTimeTaken()->toBeGreaterThan(0)->toBeLessThan(10000)
        ->getTimeRemaining()->toBeGreaterThan(0)->toBeLessThan(10000)
        ->getStart()->toBeGreaterThan($start)->toBeLessThan($end)
        ->getEnd()->toBeGreaterThan($start)->toBeLessThan($end);
});

test('a story cut short by a timeout will still run tearDown', function () {

    $ran = Collection::make();

    $story = Story::make('timed test')
        ->can()
        ->action(fn () => usleep(1000001))
        ->assert(fn () => null)
        ->tearDown(fn () => $ran[] = 'tearDown')
        ->timeout(1);

    try {
        $story->run();

        $this->fail();
    } catch (ExpectationFailedException $e) {
        //
    }

    expect($ran->toArray())->toBe([
        'tearDown',
    ]);
});
