<?php

use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Helpers\StoryAliases;
use BradieTilley\Stories\Story;

test('a story can have conditionable logic', function (string $text) {
    $ran = collect();

    $story = story()
        ->setUp(fn () => $ran[] = 'setUp')
        ->before(fn () => $ran[] = 'before')
        ->after(fn () => $ran[] = 'after')
        ->when(fn () => ($text === 'true'), function () use ($ran) {
            $ran[] = 'when:true';
        }, function () use ($ran) {
            $ran[] = 'when:false';
        })
        ->unless(fn () => ($text !== 'true'), function () use ($ran) {
            $ran[] = 'unless:true';
        }, function () use ($ran) {
            $ran[] = 'unless:false';
        })
        ->lazyWhen(fn () => ($text === 'true'), fn () => $ran[] = 'lazyWhen:true', fn () => $ran[] = 'lazyWhen:false')
        ->lazyUnless(fn () => ($text !== 'true'), fn () => $ran[] = 'lazyUnless:true', fn () => $ran[] = 'lazyUnless:false');

    $story->process();

    expect($ran->toArray())->toBe([
        'when:'.$text,
        'unless:'.$text,
        'setUp',
        'lazyWhen:'.$text,
        'lazyUnless:'.$text,
        'before',
        'after',
    ]);
})->with([
    'true',
    'false',
]);

test('a story conditionable callback will inherit the correct story', function () {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');
    $ran = collect();

    $story = story('parent')
        ->lazyWhen(true, fn (Story $story) => $ran[] = $story->getName().':when', fn () => $ran[] = 'fail')
        ->lazyUnless(false, fn (Story $story) => $ran[] = $story->getName().':unless', fn () => $ran[] = 'fail')
        ->stories([
            story('child 1'),
            story('child 2'),
            story('child 3'),
        ])
        ->test()
        ->run();

    expect($ran->toArray())->toBe([
        'parent child 1:when',
        'parent child 1:unless',
        'parent child 2:when',
        'parent child 2:unless',
        'parent child 3:when',
        'parent child 3:unless',
    ]);
});
