<?php

use BradieTilley\Stories\Concerns\Stories;
use function BradieTilley\Stories\Helpers\action;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(Stories::class);
$ran = Collection::make();

action('do_something_in_after_each', function (string $afterEachRan) use ($ran) {
    $ran[] = 'afterEach:'.$afterEachRan;
});

afterEach(function () {
    /** @var TestCase&Stories $this */
    $name = (new ReflectionProperty($this, '__description'))->getValue($this);
    $this->action('do_something_in_after_each', [
        'afterEachRan' => $name,
    ]);
});

test('example afterEach test one')
    ->action(fn () => expect(true)->toBeTrue());

test('example afterEach test two')
    ->action(fn () => expect(true)->toBeTrue());

test('example afterEach test three')
    ->action(fn () => expect(true)->toBeTrue());

test('example afterEach can run actions via Stories trait')
    ->action(function () use ($ran) {
        expect($ran->toArray())->toBe([
            'afterEach:example afterEach test one',
            'afterEach:example afterEach test two',
            'afterEach:example afterEach test three',
            // hasn't finished yet so no afterEach for this test:
            // 'afterEach:example afterEach can run actions via Stories trait',
        ]);
    });
