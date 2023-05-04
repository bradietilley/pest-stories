<?php

use BradieTilley\Stories\Exceptions\FailedToIdentifyCallbackArgumentsException;
use BradieTilley\Stories\Exceptions\MissingRequiredArgumentsException;
use BradieTilley\Stories\Helpers\Invoker;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Story;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Tests\Fixtures\AnExampleAction;

test('invoker can invoke a basic closure', function () {
    $ran = Collection::make();

    $callback = function (string $foo, int $bar) use ($ran) {
        $ran[] = [$foo, $bar];

        return $ran->count();
    };

    $invoker = Invoker::make();

    $response = $invoker->call($callback, [
        'bar' => 456,
        'foo' => '111',
    ]);
    expect($response)->toBe(1);
    expect($ran->toArray())->toBe([
        [
            '111',
            456,
        ],
    ]);

    $response = $invoker->call($callback, [
        'bar' => 789,
        'foo' => '222',
    ]);
    expect($response)->toBe(2);
    expect($ran->toArray())->toBe([
        [
            '111',
            456,
        ],
        [
            '222',
            789,
        ],
    ]);
});

test('a story invoker will be default be the laravel container', function () {
    Story::invokeUsing(null);

    $story = story();
    expect($story->invoker())->toBeInstanceOf(Container::class);
});

test('a story invoker can be configured to be the Pest Stories Invoker', function () {
    Story::invokeUsing(Invoker::make());

    $story = story();
    expect($story->invoker())->toBeInstanceOf(Invoker::class);
});

test('a story can invoke a custom closure callback', function () {
    story()->use();
    $ran = collect();

    story()
        ->set('foo', 123)
        ->set('bar', false)
        ->call(function (int $foo, bool $bar) use ($ran) {
            $ran[] = $foo;
            $ran[] = $bar;
        }, [
            'foo' => 456,
        ]);

    expect($ran->toArray())->toBe([
        456,
        false,
    ]);
});

test('a story can invoke a custom Action __invoke method', function () {
    AnExampleAction::$ran = [];

    $story = story()->use();

    $result = $story
        ->set('abc', 111)
        ->call([new AnExampleAction(), '__invoke'], [
            'story' => $story,
            'abc' => 222,
        ]);

    expect(AnExampleAction::$ran)->toBe([
        'abc:222',
    ]);

    expect($result)->toBe(444);
});

test('a story cannot invoke a method that does not exist', function () {
    Story::invokeUsing(Invoker::make());

    story()->use()
        ->set('abc', 111)
        ->call([new AnExampleAction(), 'methodDoesNotExist']);
})->throws(
    FailedToIdentifyCallbackArgumentsException::class,
    'Failed to identify callback arguments: Method Tests\Fixtures\AnExampleAction::methodDoesNotExist() does not exist',
);

test('a story cannot invoke a method that is missing a required argument', function () {
    Story::invokeUsing(Invoker::make());

    story()->fresh()
        ->use()
        ->set('def', 111) // no abc
        ->call([new AnExampleAction(), '__invoke']);
})->throws(
    MissingRequiredArgumentsException::class,
    'Missing required arguments for callback invocation: Tests\Fixtures\AnExampleAction::__invoke(): Argument #2 ($abc) must be of type int, null given',
);
// ->throws(
//     CallbackNotCallableException::class,
//     'Cannot call non-callable callback: method: `Tests\Fixtures\AnExampleAction::__invoke()`',
// );
