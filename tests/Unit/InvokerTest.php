<?php

use BradieTilley\Stories\Exceptions\CallbackNotCallableException;
use BradieTilley\Stories\Exceptions\FailedToIdentifyCallbackArgumentsException;
use BradieTilley\Stories\Exceptions\MissingRequiredArgumentsException;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Helpers\StoryInvoker;
use BradieTilley\Stories\Story;
use Illuminate\Container\Container as ApplicationContainer;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Collection;
use Tests\Fixtures\AnExampleAction;
use Tests\Fixtures\AnExampleActionWithManyArguments;
use Tests\Fixtures\AnExampleClassWithPrivateMethod;
use Tests\Fixtures\NonActionExample;

test('invoker can invoke a basic closure', function () {
    $ran = Collection::make();

    $callback = function (string $foo, int $bar) use ($ran) {
        $ran[] = [$foo, $bar];

        return $ran->count();
    };

    $invoker = StoryInvoker::make();

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

test('a story invoker will can be configured to be the laravel container', function () {
    Story::invokeUsing(ApplicationContainer::getInstance());

    $story = story();
    expect($story->invoker())->toBeInstanceOf(Container::class);

    Story::invokeUsing(null);
});

test('a story invoker will can be configured to be the laravel container via app()', function () {
    Story::invokeUsing(app());

    $story = story();
    expect($story->invoker())->toBeInstanceOf(Container::class);

    Story::invokeUsing(null);
});

test('a story invoker will by default be the Pest Stories Invoker', function () {
    Story::invokeUsing(null);

    $story = story();
    expect($story->invoker())->toBeInstanceOf(StoryInvoker::class);
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
    Story::invokeUsing(null);

    story()->use()
        ->set('abc', 111)
        ->call([new AnExampleAction(), 'methodDoesNotExist']);
})->throws(
    FailedToIdentifyCallbackArgumentsException::class,
    'Failed to identify callback arguments: Method Tests\Fixtures\AnExampleAction::methodDoesNotExist() does not exist',
);

test('a story cannot invoke a method that is missing a required argument', function () {
    Story::invokeUsing(null);

    story()->fresh()
        ->use()
        ->set('def', 111) // no abc
        ->call([new AnExampleAction(), '__invoke']);
})->throws(
    MissingRequiredArgumentsException::class,
    'Missing required arguments for callback invocation: Tests\Fixtures\AnExampleAction::__invoke(): Argument #2 ($abc) must be of type int, null given',
);

test('an invoked callback that throws an internal exception will bubble out of the invoker', function () {
    Story::invokeUsing(null);

    story()->fresh()
        ->use()
        ->set('def', 111) // no abc
        ->call(function () {
            throw new InvalidArgumentException('An example error that still bubbles outside of Invoker');
        });
})->throws(
    InvalidArgumentException::class,
    'An example error that still bubbles outside of Invoker',
);

test('can invoke methods on objects', function (string $visibility, string $static, int $return, ?string $expectError = null) {
    Story::invokeUsing(null);

    $class = ($static === 'static') ? AnExampleClassWithPrivateMethod::class : new AnExampleClassWithPrivateMethod();
    $static = ucfirst($static);
    $method = "{$visibility}InvokeMe{$static}";

    $value = null;
    $exception = null;

    try {
        $value = story()->fresh()
            ->use()
            ->call([$class, $method]);
    } catch (CallbackNotCallableException $exception) {
    }

    if ($expectError === null) {
        expect($exception)->toBeNull();
        expect($value)->toBe($return);
    } else {
        expect($exception->getMessage())->toBe($expectError);
        expect($value)->toBeNull();
    }
})->with([
    'private method' => ['private', '', 1, 'Cannot call non-callable callback: method: `Tests\Fixtures\AnExampleClassWithPrivateMethod::privateInvokeMe()`'],
    'protected method' => ['protected', '', 2, 'Cannot call non-callable callback: method: `Tests\Fixtures\AnExampleClassWithPrivateMethod::protectedInvokeMe()`'],
    'public method' => ['public', '', 3, null],
    'private static method' => ['private', 'static', 4, 'Cannot call non-callable callback: method: `Tests\Fixtures\AnExampleClassWithPrivateMethod::privateInvokeMeStatic()`'],
    'protected static method' => ['protected', 'static', 5, 'Cannot call non-callable callback: method: `Tests\Fixtures\AnExampleClassWithPrivateMethod::protectedInvokeMeStatic()`'],
    'public static method' => ['public', 'static', 6, null],
]);

$benchmarks = Collection::make([]);

test('a story invoker benchmark test', function () use ($benchmarks) {
    $callback = [AnExampleActionWithManyArguments::make(), '__invoke'];
    $arguments = [
        'abc' => 3534,
        'def' => 'erg erjng ierng iuerngeiru',
        'ghi' => new NonActionExample(),
        'jkl' => AnExampleAction::make(),
    ];

    $invoker = new StoryInvoker();

    $avg = Benchmark::measure(fn () => $invoker->call($callback, $arguments), iterations: 1000);
    $benchmarks['invoker'] = $avg;

    expect(true)->toBeTrue();
});

test('a container invoker benchmark test', function () use ($benchmarks) {
    $callback = [AnExampleActionWithManyArguments::make(), '__invoke'];
    $arguments = [
        'abc' => 3534,
        'def' => 'erg erjng ierng iuerngeiru',
        'ghi' => new NonActionExample(),
        'jkl' => AnExampleAction::make(),
    ];

    $invoker = ApplicationContainer::getInstance();

    $avg = Benchmark::measure(fn () => $invoker->call($callback, $arguments), iterations: 1000);
    $benchmarks['container'] = $avg;

    expect(true)->toBeTrue();
});

test('the invoker runs quicker than the laravel container - invoker benchmark test', function () use ($benchmarks) {
    /**
     * Skip this test when xdebug coverage is enabled,
     */
    if (env('XDEBUG_MODE', ini_get('xdebug.mode')) === 'coverage') {
        expect(true)->toBeTrue();

        return;
    }

    expect($benchmarks)->toHaveCount(2)->toHaveKeys([
        'invoker',
        'container',
    ]);

    expect($benchmarks['invoker'])
        ->toBeLessThan(
            $benchmarks['container'],
            sprintf(
                'Failed asserting the invoker (ran in %ss) was quicker than the container (ran in %ss)',
                $benchmarks['invoker'],
                $benchmarks['container'],
            )
        );
});
