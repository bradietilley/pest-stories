<?php

use BradieTilley\Stories\Concerns\Stories;
use BradieTilley\Stories\Story;
use PHPUnit\Framework\ExpectationFailedException;

uses(Stories::class);

test('an expectation chain can be formed on a test story call')
    ->action(fn (Story $story) => $story->setData('abc', 123))
    ->expects('abc')
    ->toBe(123);

test('an expectation chain can be formed on a test story call as a callback')
    ->action(fn (Story $story) => $story->setData('abc', 123))
    ->expects(fn () => 456)
    ->toBe(456);

test('an expectation chain can fail')
    ->action(fn (Story $story) => $story->setData('abc', 123))
    ->throws(ExpectationFailedException::class, 'Failed asserting that 123 is identical to 456.')
    ->expects('abc')
    ->toBe(456);
