<?php

use BradieTilley\Stories\Concerns\Stories;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Story;
use PHPUnit\Framework\ExpectationFailedException;

uses(Stories::class);

test('an expectation chain can be formed on a test story call')
    ->action(fn (Story $story) => $story->set('abc', 123))
    ->expects('abc')
    ->toBe(123);

test('an expectation chain can be formed on a test story call as a callback')
    ->action(fn (Story $story) => $story->set('abc', 123))
    ->expects(fn () => 456)
    ->toBe(456);

test('an expectation chain can fail')
    ->action(fn (Story $story) => $story->set('abc', 123))
    ->throws(ExpectationFailedException::class, 'Failed asserting that 123 is identical to 456.')
    ->expects('abc')
    ->toBe(456);

test('an expectation chain can be built within an action')
    ->action(function () {
        story()->set('abc', 123);
        story()->set('def', 456);
        story()->set('ghi', 789);
    })
    ->action(function () {
        story()->expects('abc')->toBe(123);
        story()->expects('def')->toBe(456);
        story()->expects('ghi')->toBe(789);
    });
