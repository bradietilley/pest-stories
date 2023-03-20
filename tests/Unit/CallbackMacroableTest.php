<?php

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Assertion;
use BradieTilley\Stories\Story;

test('a callback can be given a macro', function (string $for) {
    $log = collect();

    $others = collect([
        Action::class,
        Assertion::class,
        Story::class,
    ])->flip()->forget($for)->keys()->toArray();

    /**
     * Test that the macro can be added and run
     */
    $for::macro('log', function () use ($log) {
        /** @var Action|Assertion|Story $this */
        $log[] = 'logged '.$this->getName();
    });

    // Use the macro
    $for::make('do_something')->log();

    // Assert the macro worked
    expect($log->toArray())->toBe([
        'logged do_something',
    ]);

    /**
     * Test that the macro does not apply to other callback classes
     */
    foreach ($others as $other) {
        try {
            $other::make('test')->log();
            $this->fail();
        } catch (BadMethodCallException $exception) {
            expect($exception->getMessage())->toBe(
                sprintf('Method %s::log does not exist.', $other)
            );
        }
    }
})
    ->with([
        Action::class,
        Assertion::class,
        Story::class,
    ]);
