<?php

use BradieTilley\StoryBoard\Exceptions\InvalidMagicMethodHandlerException;
use BradieTilley\StoryBoard\Exceptions\InvalidMagicMethodHandlerException as Error;
use BradieTilley\StoryBoard\Exceptions\StoryBoardException;

test('can instantiate an InvalidMagicMethodHandlerException for a missing property handler', function () {
    $exception = new Error('example', Error::TYPE_PROPERTY);

    expect($exception->getMessage())->toBe('Failed to locate the `$example` magic property shandler');
});

test('can instantiate an InvalidMagicMethodHandlerException for a missing method handler', function () {
    $exception = new Error('example', Error::TYPE_METHOD);

    expect($exception->getMessage())->toBe('Failed to locate the `example()` magic method shandler');
});

test('can instantiate an InvalidMagicMethodHandlerException for a missing static method handler', function () {
    $exception = new Error('example', Error::TYPE_STATIC_METHOD);

    expect($exception->getMessage())->toBe('Failed to locate the `::example()` magic static method shandler');
});

test('can thrown an InvalidMagicMethodHandlerException via StoryBoardException', function () {
    throw StoryBoardException::invalidMagicMethodHandlerException('something', 'property');
})->throws(InvalidMagicMethodHandlerException::class, 'Failed to locate the `$something` magic property shandler');
