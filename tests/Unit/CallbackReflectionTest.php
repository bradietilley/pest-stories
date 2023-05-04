<?php

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Exceptions\FailedToIdentifyCallbackArgumentsException;
use BradieTilley\Stories\Helpers\ReflectionCallback;
use Illuminate\Support\Str;

test('a callback reflection can identify arguments of a closure', function () {
    $callable = function (string $abc, int $def, bool $ghi) {
    };

    $expect = [
        'abc',
        'def',
        'ghi',
    ];
    $actual = ReflectionCallback::make($callable)->arguments();

    expect($actual)->toBe($expect);
});

test('a callback reflection can identify arguments of a global function', function () {
    $callable = 'test';

    $expect = [
        'description',
        'closure',
    ];
    $actual = ReflectionCallback::make($callable)->arguments();

    expect($actual)->toBe($expect);
});

test('a callback reflection can identify arguments of a class method', function () {
    $callable = [
        Action::class,
        'process',
    ];

    $expect = [
        'story',
        'arguments',
        'variable',
    ];
    $actual = ReflectionCallback::make($callable)->arguments();

    expect($actual)->toBe($expect);
});

test('cannot reflect a function that does not exist', function () {
    ReflectionCallback::make('a_function_that_does_not_exist')->arguments();
})->throws(
    FailedToIdentifyCallbackArgumentsException::class,
    'Failed to identify callback arguments: Function a_function_that_does_not_exist() does not exist',
);

test('cannot reflect a class method that does not exist', function () {
    ReflectionCallback::make([Action::class, 'aMethodThatDoesNotExist'])->arguments();
})->throws(
    FailedToIdentifyCallbackArgumentsException::class,
    'Failed to identify callback arguments: Method BradieTilley\Stories\Action::aMethodThatDoesNotExist() does not exist',
);

test('cannot reflect a class method that is invalid', function () {
    ReflectionCallback::make([
        'only one argument',
    ])->arguments();
})->throws(
    FailedToIdentifyCallbackArgumentsException::class,
    'Failed to identify callback arguments: Callback reflection format not supported',
);
test('can generate a unique name for a closure', function () {
    $closure = function () {

    };

    $line = __LINE__ - 4;
    $file = __FILE__;

    $expect = "inline@{$file}:{$line}";
    $callback = ReflectionCallback::make($closure);

    $actual = $callback->uniqueName();
    expect($actual)->toStartWith($expect);

    $actual = Str::replaceFirst($expect, '', $actual);
    expect($actual)->toMatch('/^\[[a-z0-9]{8}\]$/i');
});
