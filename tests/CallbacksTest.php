<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

test('a story can have a before callback', function () {
    /**
     * Same level
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->task(fn () => null)
        ->check(fn () => null)
        ->before(fn (Story $story) => $ran[] = 'before:' . $story->getName())
        ->run();
    expect($ran)->toHaveCount(1)->first()->toBe('before:parent');

    /**
     * Inherited
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->task(fn () => null)
        ->check(fn () => null)
        ->before(fn (Story $story) => $ran[] = 'before:' . $story->getName())
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
        ->task(fn () => null)
        ->check(fn () => null)
        ->after(fn (Story $story) => $ran[] = 'after:' . $story->getName())
        ->run();
    expect($ran)->toHaveCount(1)->first()->toBe('after:parent');

    /**
     * Inherited
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->task(fn () => null)
        ->check(fn () => null)
        ->after(fn (Story $story) => $ran[] = 'after:' . $story->getName())
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
        ->task(fn () => null)
        ->check(fn () => null)
        ->setUp(fn (Story $story) => $ran[] = 'setUp:' . $story->getName())
        ->run();
    expect($ran)->toHaveCount(1)->first()->toBe('setUp:parent');

    /**
     * Inherited
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->task(fn () => null)
        ->check(fn () => null)
        ->setUp(fn (Story $story) => $ran[] = 'setUp:' . $story->getName())
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
        ->task(fn () => null)
        ->check(fn () => null)
        ->tearDown(fn (Story $story) => $ran[] = 'tearDown:' . $story->getName())
        ->run();
    expect($ran)->toHaveCount(1)->first()->toBe('tearDown:parent');

    /**
     * Inherited
     */
    $ran = Collection::make();
    Story::make('parent')
        ->can()
        ->task(fn () => null)
        ->check(fn () => null)
        ->tearDown(fn (Story $story) => $ran[] = 'tearDown:' . $story->getName())
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
            ->task(fn () => null)
            ->check(fn () => throw new InvalidArgumentException('test error'))
            ->tearDown(fn (Story $story, Throwable $e) => $ran[] = 'tearDown:' . $e->getMessage())
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