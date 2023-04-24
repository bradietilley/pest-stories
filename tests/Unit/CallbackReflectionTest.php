<?php

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Helpers\CallbackReflection;

test('a callback reflection can identify arguments of a closure', function () {
    $callable = function (string $abc, int $def, bool $ghi) {
    };

    $expect = [
        'abc',
        'def',
        'ghi',
    ];
    $actual = CallbackReflection::make($callable)->arguments();

    expect($actual)->toBe($expect);
});

test('a callback reflection can identify arguments of a global function', function () {
    $callable = 'test';

    $expect = [
        'description',
        'closure',
    ];
    $actual = CallbackReflection::make($callable)->arguments();

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
    $actual = CallbackReflection::make($callable)->arguments();

    expect($actual)->toBe($expect);
});
