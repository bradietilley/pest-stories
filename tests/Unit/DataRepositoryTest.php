<?php

use BradieTilley\Stories\Concerns\Stories;
use function BradieTilley\Stories\Helpers\story;

uses(Stories::class);

test('a data repo can get and set values')
    ->action(function () {
        $repo = story()->data;

        $repo->set('abc', 123);
        expect($repo->get('abc'))->toBe(123);
        expect($repo->getOr('def', default: 'not set'))->toBe('not set');

        $repo->set('def', 456);
        expect($repo->get('def'))->toBe(456);
    });

test('a data repo can check if it has values')
    ->action(function () {
        $repo = story()->data;

        expect($repo->has('abc'))->toBeFalse();
        expect($repo->has('def'))->toBeFalse();

        $repo->set('abc', 123);

        expect($repo->has('abc'))->toBeTrue();
        expect($repo->has('def'))->toBeFalse();

        $repo->set('def', 456);

        expect($repo->has('abc'))->toBeTrue();
        expect($repo->has('def'))->toBeTrue();
    });

test('a data repo can remember values')
    ->action(function () {
        $repo = story()->data;

        expect($repo->has('abc'))->toBeFalse();
        expect($repo->has('def'))->toBeFalse();

        $value = $repo->remember('abc', fn () => 'my text');
        expect($value)->toBe('my text');

        expect($repo->get('abc'))->toBe('my text');
        expect($repo->has('def'))->toBeFalse();

        $value = story()->remember('abc', fn () => 'my second text');
        expect($value)->toBe('my text');

        expect($repo->get('abc'))->toBe('my text');
        expect($repo->has('def'))->toBeFalse();
    });

test('a data repo can merge in bulk data')
    ->action(function () {
        $repo = story()->data;

        expect($repo->has('a'))->toBeFalse();
        expect($repo->has('b'))->toBeFalse();
        expect($repo->has('c'))->toBeFalse();

        $repo->merge([
            'a' => '1',
            'b' => '2',
            'c' => '3',
        ]);

        expect($repo->get('a'))->toBe('1');
        expect($repo->get('b'))->toBe('2');
        expect($repo->get('c'))->toBe('3');
    });
