<?php

use BradieTilley\Stories\Concerns\Stories;
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\story;
use Illuminate\Support\Collection;

uses(Stories::class);
$ran = Collection::make();

action('do_something_in_before_all', function () use ($ran) {
    $ran[] = 'ran_once';
}, 'beforeAllValue');

beforeAll(function () {
    story()->action('do_something_in_before_all');
});

test('example beforeAll test one')
    ->action(fn () => expect($ran->toArray())->toBe([
        'ran_once',
    ]))
    ->action(function () {
        /**
         * This story won't have the value returned from beforeAll as
         * it was a temporary story and its data repository was not
         * shared with this story.
         */
        expect($this->has('beforeAllValue'))->toBeFalse();
    });

test('example beforeAll test two')
    ->action(fn () => expect($ran->toArray())->toBe([
        'ran_once',
    ]))
    ->action(function () {
        /**
         * This story won't have the value returned from beforeAll as
         * it was a temporary story and its data repository was not
         * shared with this story.
         */
        expect($this->has('beforeAllValue'))->toBeFalse();
    });

test('example beforeAll test three')
    ->action(fn () => expect($ran->toArray())->toBe([
        'ran_once',
    ]))
    ->action(function () {
        /**
         * This story won't have the value returned from beforeAll as
         * it was a temporary story and its data repository was not
         * shared with this story.
         */
        expect($this->has('beforeAllValue'))->toBeFalse();
    });
