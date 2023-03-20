<?php

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Assertion;
use BradieTilley\Stories\Exceptions\CallbackFetchNotFoundException;
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\assertion;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Story;

test('callback classes can be created with an eager name', function () {
    $story = story('a name');
    expect($story->getName())->toBe('a name');
});

test('callback classes can be created with a lazy name', function () {
    $story = story();
    expect($story->getName())->toBe('');

    $story->setName('a name');
    expect($story->getName())->toBe('a name');
});

test('callback classes can be created with an eager callback', function () {
    $ran = collect();
    $action = action('do_something', fn () => $ran[] = 'Nice');

    $result = $action->boot();

    expect($ran)->toHaveCount(1)->toArray()->toBe([
        'Nice',
    ]);
    expect($result)->toBe('Nice');
});

test('callback classes can be created with a lazy callback', function () {
    $ran = collect();
    $action = action('do_something')->as(fn () => $ran[] = 'Nice');

    $result = $action->boot();

    expect($ran)->toHaveCount(1)->toArray()->toBe([
        'Nice',
    ]);
    expect($result)->toBe('Nice');
});

test('callback classes can be created with arguments', function () {
    $ran = collect();
    $action = action('do_something')->as(fn (string $a, int $b) => $ran[$a] = $b);

    $result = $action->boot([
        'a' => 'foo',
        'b' => 2,
    ]);

    expect($ran)->toHaveCount(1)->toArray()->toBe([
        'foo' => 2,
    ]);
    expect($result)->toBe(2);
});

test('callback classes can have additional arguments set and get', function () {
    $ran = collect();
    $action = action()
        ->before(function (Action $action) use ($ran) {
            $action->set('a', 1);
            $action->set('b', 2);

            $ran[] = 'before';
        })
        ->as(function (Action $action) use ($ran) {
            /** @var Action $this */
            $action->set('b', $action->get('b', 0) + 1);
            $action->set('c', $action->get('c', 5) + 1);
            $action->set('d', 10);

            $ran[] = 'as';
        })
        ->after(function (Action $action, int $a, int $b, int $c, int $d) use ($ran) {
            /** @var Action $this */
            $ran[] = 'after';

            $ran[] = "a:{$a}";
            $ran[] = "b:{$b}";
            $ran[] = "c:{$c}";
            $ran[] = "d:{$d}";
        });

    $action->boot();

    expect($ran)->toHaveCount(7)->toArray()->toBe([
        'before',
        'as',
        'after',
        'a:1',
        'b:3',
        'c:6',
        'd:10',
    ]);
});

test('callback classes can be created with before and after callbacks', function () {
    $ran = collect();

    $action = action('do_something')
        ->after(fn () => $ran[] = 'after')
        ->as(fn () => $ran[] = 'as')
        ->before(fn () => $ran[] = 'before');

    $result = $action->boot();

    expect($ran)->toHaveCount(3)->toArray()->toBe([
        'before',
        'as',
        'after',
    ]);
    expect($result)->toBe('as');
});

test('callback classes can be stored by name and retrieved later', function () {
    $action = action('do_something');
    $assertion = assertion('do_something');
    $story = story('do_something');

    expect(Action::fetch('do_something'))->toBe($action);
    expect(Assertion::fetch('do_something'))->toBe($assertion);
    expect(Story::fetch('do_something'))->toBe($story);
});

test('action classes that do not exist cannot be fetched', function () {
    Action::fetch('do_something');
})->throws(CallbackFetchNotFoundException::class, 'Cannot find the action callback with name `do_something`');

test('assertion classes that do not exist cannot be fetched', function () {
    Assertion::fetch('do_something');
})->throws(CallbackFetchNotFoundException::class, 'Cannot find the assertion callback with name `do_something`');

test('story classes that do not exist cannot be fetched', function () {
    Story::fetch('do_something');
})->throws(CallbackFetchNotFoundException::class, 'Cannot find the story callback with name `do_something`');
