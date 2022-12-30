<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Result;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

test('a result given from a story can be referenced in the checkers', function () {
    $ran = Collection::make([]);
    $randomStrings = Collection::make([
        Str::random(8),
        Str::random(8),
    ]);
    $randomStringsClone = $randomStrings->collect();

    $story = Story::make()
        ->name('parent')
        ->before(function (Story $story) use ($ran) {
            $ran[] = "before:{$story->getName()}";

            expect($story->getResult()->hasValue())->toBeFalse();
            expect($story->getResult()->getValue())->toBeNull();
            expect($story->getResult()->getError())->toBeNull();
        })
        ->check(
            function (Story $story) use ($ran, $randomStringsClone) {
                $ran[] = "can:{$story->getName()}";
    
                expect($story->getResult()->hasValue())->toBeTrue();
                expect($story->getResult()->getValue())->toBe($randomStringsClone->first());
                expect($story->getResult()->getError())->toBeNull();

            },
            function (Story $story) use ($ran, $randomStringsClone) {
                $ran[] = "cannot:{$story->getName()}";
    
                expect($story->getResult()->hasValue())->toBeTrue();
                expect($story->getResult()->getValue())->toBe($randomStringsClone->first());
                expect($story->getResult()->getError())->toBeNull();
            },
        )
        ->after(function (Story $story) use ($ran, $randomStringsClone) {
            $ran[] = "after:{$story->getName()}";

            expect($story->getResult()->hasValue())->toBeTrue();
            expect($story->getResult()->getValue())->toBe($randomStringsClone->first());
            expect($story->getResult()->getError())->toBeNull();
        })
        ->task(function (Story $story) use ($ran, $randomStrings) {
            $ran[] = "task:{$story->getName()}";

            $result = $randomStrings->shift();

            expect($story->getResult()->hasValue())->toBeFalse();
            expect($story->getResult()->getValue())->toBeNull();
            expect($story->getResult()->getError())->toBeNull();

            return $result;
        })
        ->stories([
            Story::make('can')->can(),
            Story::make('cannot')->cannot(),
        ]);
    
    foreach ($story->allStories() as $story) {
        $story->boot()->assert();

        // Remove first item so that the second test can grab the relevant string with ->first()
        $randomStringsClone->shift();
    }

    /**
     * This assertion is just to double check that all callbacks were correctly
     * run, albeit separately tested and presumed to work. Now we can be certain
     * all expectations in the callbacks are also run
     */
    expect($ran->toArray())->toBe([
        'before:can',
        'task:can',
        'after:can',
        'can:can',
        'before:cannot',
        'task:cannot',
        'after:cannot',
        'cannot:cannot',
    ]);
});

test('when an error occurs during a callback, the result of the story contains a reference to the error', function (string $throwsIn) {
    $story = Story::make()
        ->name('tester')
        ->before(fn () => ($throwsIn === 'before') ? throw new \Exception('Dummy Test Exception via before') : null)
        ->task(fn () => ($throwsIn === 'task') ? throw new \Exception('Dummy Test Exception via task') : null)
        ->task(fn () => ($throwsIn === 'after') ? throw new \Exception('Dummy Test Exception via after') : null)
        ->check(fn () => ($throwsIn === 'check') ? throw new \Exception('Dummy Test Exception via check') : null)
        ->can();
    
    try {
        $story->boot();
        $story->assert();
    } catch (\Exception $e) {
    }

    $result = $story->getResult();
    
    expect($result)->errored()->toBeTrue()
        ->and($result->getError())->not()->toBeNull()
        ->and($result->getError())->getMessage()->toBe('Dummy Test Exception via ' . $throwsIn);
})->with([
    'before',
    'task',
    'after',
    'check',
]);

test('checkers can inject the raw result as an argument', function () {
    $results = Collection::make();

    Story::make()
        ->name('tester')
        ->task(fn () => 1234567890)
        // main thing is $result is an int, not Result object 
        ->check(fn (int $result) => $results[] = $result)
        ->can()
        ->boot()
        ->assert();

    expect($results)->toHaveCount(1)
        ->and($results[0])->toBe(1234567890);
});
