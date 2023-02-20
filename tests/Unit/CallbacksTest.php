<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Traits\HasCallbacks;
use Illuminate\Support\Collection;

test('a story can have a before callback', function () {
    /**
     * Same level
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->assert(fn () => null)
        ->action(fn () => null)
        ->before(fn (Story $story) => $ran[] = 'before:'.$story->getName())
        ->run();
    expect($ran)->toHaveCount(1)->first()->toBe('before:parent');

    /**
     * Inherited
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->assert(fn () => null)
        ->action(fn () => null)
        ->before(fn (Story $story) => $ran[] = 'before:'.$story->getName())
        ->stories([
            Story::make('child'),
        ])
        ->storiesAll
        ->each(fn (Story $story) => $story->run());
    expect($ran)->toHaveCount(1)->first()->toBe('before:child');
});

test('a story can have a after callback', function () {
    /**
     * Same level
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->action(fn () => null)
        ->assert(fn () => null)
        ->after(fn (Story $story) => $ran[] = 'after:'.$story->getName())
        ->run();
    expect($ran)->toHaveCount(1)->first()->toBe('after:parent');

    /**
     * Inherited
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->action(fn () => null)
        ->assert(fn () => null)
        ->after(fn (Story $story) => $ran[] = 'after:'.$story->getName())
        ->stories([
            Story::make('child'),
        ])
        ->storiesAll
        ->each(fn (Story $story) => $story->run());
    expect($ran)->toHaveCount(1)->first()->toBe('after:child');
});

test('a story can have a setUp callback', function () {
    /**
     * Same level
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->action(fn () => null)
        ->assert(fn () => null)
        ->setUp(fn (Story $story) => $ran[] = 'setUp:'.$story->getName())
        ->run();
    expect($ran)->toHaveCount(1)->first()->toBe('setUp:parent');

    /**
     * Inherited
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->action(fn () => null)
        ->assert(fn () => null)
        ->setUp(fn (Story $story) => $ran[] = 'setUp:'.$story->getName())
        ->stories([
            Story::make('child'),
        ])
        ->storiesAll
        ->each(fn (Story $story) => $story->run());
    expect($ran)->toHaveCount(1)->first()->toBe('setUp:child');
});

test('a story can have a tearDown callback', function () {
    /**
     * Same level
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->action(fn () => null)
        ->assert(fn () => null)
        ->tearDown(fn (Story $story) => $ran[] = 'tearDown:'.$story->getName())
        ->run();
    expect($ran)->toHaveCount(1)->first()->toBe('tearDown:parent');

    /**
     * Inherited
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->action(fn () => null)
        ->assert(fn () => null)
        ->tearDown(fn (Story $story) => $ran[] = 'tearDown:'.$story->getName())
        ->stories([
            Story::make('child'),
        ])
        ->storiesAll
        ->each(fn (Story $story) => $story->run());
    expect($ran)->toHaveCount(1)->first()->toBe('tearDown:child');

    try {
        /**
         * Inherited with error
         */
        $ran = Collection::make();
        Story::make('parent')
            ->can()
            ->action(fn () => null)
            ->assert(fn () => throw new InvalidArgumentException('test error'))
            ->tearDown(fn (Story $story, Throwable $e) => $ran[] = 'tearDown:'.$e->getMessage())
            ->stories([
                Story::make('child'),
            ])
            ->storiesAll
            ->each(fn (Story $story) => $story->run());

        $this->fail();
    } catch (Throwable $e) {
        //
    }
    expect($ran)->toHaveCount(1)->first()->toBe('tearDown:test error');
});

test('you cannot setup or teardown a story more than once', function () {
    $ran = Collection::make();

    $story = Story::make()
        ->setUp(fn () => $ran[] = 'setUp')
        ->tearDown(fn () => $ran[] = 'tearDown');

    $reflection = new ReflectionClass($story);

    $runSetUp = $reflection->getMethod('runSetUp');
    $runSetUp->setAccessible(true);

    $runTearDown = $reflection->getMethod('runTearDown');
    $runTearDown->setAccessible(true);

    // Run twice
    $runSetUp->invoke($story);
    $runSetUp->invoke($story);

    // Run twice
    $runTearDown->invoke($story);
    $runTearDown->invoke($story);

    expect($ran->toArray())->toBe([
        'setUp',
        'tearDown',
    ]);
});

test('an object with HasCallbacks will return only the additional parameters by default', function () {
    $additional = [
        1,
        2,
        3,
    ];

    $class = new class()
    {
        use HasCallbacks;
    };

    expect($class->getParameters($additional))->toBe($additional);
});

test('an object with HasCallbacks but without WithInheritance will safely not inherit', function () {
    $class = new class()
    {
        use HasCallbacks;
    };
    $class->inheritCallbacks();

    // no error
    expect(true)->toBeTrue();
});
