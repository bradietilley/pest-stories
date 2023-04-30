<?php

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Concerns\Stories;
use BradieTilley\Stories\Exceptions\ProxyDataUnknownClassTypeException;
use function BradieTilley\Stories\Helpers\story;
use Tests\Fixtures\AnExampleActionWithProxiedData;
use Tests\Fixtures\AnExampleClassWithProxiesData;

uses(Stories::class);

test('an action can proxy method calls to the data repository')
    ->action(AnExampleActionWithProxiedData::make())
    ->action(function ($proxiedData) {
        expect($proxiedData)->toBe([
            'abc' => 123,
            'def' => 456,
            'ghi' => true,
            'jkl' => [
                7,
                8,
                9,
            ],
            'foo' => 'updated:bar',
        ]);

        $story = story();

        expect($story->has('abc'))->toBeFalse();
        expect($story->has('def'))->toBeFalse();
        expect($story->has('ghi'))->toBeFalse();
        expect($story->has('jkl'))->toBeFalse();
        expect($story->has('foo'))->toBeFalse();
    });

test('a story can proxy method calls to the data repository')
    ->action(function () {
        $story = story();

        $story->abc(123)->def(456)->ghi()->jkl(7, 8, 9);

        $story->set('foo', 'bar');
        $story->foo = 'updated:'.$story->foo;

        return $story->all();
    }, variable: 'proxiedData')
    ->action(function ($proxiedData, Action $action) {
        expect($proxiedData)->toBe([
            'abc' => 123,
            'def' => 456,
            'ghi' => true,
            'jkl' => [
                7,
                8,
                9,
            ],
            'foo' => 'updated:bar',
        ]);

        expect($action->internal->has('abc'))->toBeFalse();
        expect($action->internal->has('def'))->toBeFalse();
        expect($action->internal->has('ghi'))->toBeFalse();
        expect($action->internal->has('jkl'))->toBeFalse();
        expect($action->internal->has('foo'))->toBeFalse();
    });

test('an exception will be thrown on an unknown class that uses ProxiesData')
    ->action(function () {
        $class = new AnExampleClassWithProxiesData();
        $class->abc(123);
    })
    ->throws(ProxyDataUnknownClassTypeException::class, 'Unknown class thpe for ProxiesData class `Tests\Fixtures\AnExampleClassWithProxiesData');
