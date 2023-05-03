<?php

use BradieTilley\Stories\Concerns\Stories;
use function BradieTilley\Stories\Helpers\action;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(Stories::class);
$ran = Collection::make();

action('do_something_in_before_each', function (string $testName) use ($ran) {
    $ran[] = 'beforeEach:'.$testName;

    return $testName;
}, variable: 'beforeEachRan');

beforeEach(function () {
    /** @var TestCase&Stories $this */
    $name = (new ReflectionProperty($this, '__description'))->getValue($this);
    $this->action('do_something_in_before_each', [
        'testName' => $name,
    ]);
});

test('example beforeEach test one')
    ->action(fn (string $beforeEachRan) => expect($beforeEachRan)->toBe('example beforeEach test one'));

test('example beforeEach test two')
    ->action(fn (string $beforeEachRan) => expect($beforeEachRan)->toBe('example beforeEach test two'));

test('example beforeEach test three')
    ->action(fn (string $beforeEachRan) => expect($beforeEachRan)->toBe('example beforeEach test three'));

test('example beforeEach can run actions via Stories trait')
    ->action(function () use ($ran) {
        expect($ran->toArray())->toBe([
            'beforeEach:example beforeEach test one',
            'beforeEach:example beforeEach test two',
            'beforeEach:example beforeEach test three',
            'beforeEach:example beforeEach can run actions via Stories trait',
        ]);
    });
