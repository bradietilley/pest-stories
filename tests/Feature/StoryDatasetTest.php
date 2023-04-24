<?php

use BradieTilley\Stories\Concerns\Stories;
use function BradieTilley\Stories\Helpers\action;
use BradieTilley\Stories\Story;

uses(Stories::class);

class AnonymousDatasetTester
{
    public static array $ran = [];

    public static array $ran2 = [];

    public static array $ran3 = [];
}

action('do_something_with_datasets')->as(function (string $word, Story $story) {
    AnonymousDatasetTester::$ran[] = $word;

    expect($word)->toBeIn([
        'test',
        'foo',
        'bar',
    ]);
})->dataset();

test('a test can continue to use datasets like in normal pest')
    ->action('do_something_with_datasets')
    ->with([
        'test',
        'foo',
        'bar',
    ]);

test('all 3 datasets from the previous test were correctly recorded', function () {
    expect(AnonymousDatasetTester::$ran)->toBe([
        'test',
        'foo',
        'bar',
    ]);
});

action('do_something_with_complex_datasets')->as(function (string $word, int $number, ?bool $trinary, Story $story) {
    AnonymousDatasetTester::$ran2[] = $word;
    AnonymousDatasetTester::$ran2[] = $number;
    AnonymousDatasetTester::$ran2[] = $trinary;

    expect($word)->toBeIn([
        'test',
        'foo',
        'bar',
    ]);

    expect($number)->toBeIn([
        123,
        456,
        789,
    ]);
})->dataset();

test('a test can continue to use datasets like in normal pest with complex datasets')
    ->action('do_something_with_complex_datasets')
    ->with([
        ['test', 123, true],
        ['foo', 456, false],
        ['bar', 789, null],
    ]);

test('all 3 complex datasets from the previous test were correctly recorded', function () {
    expect(AnonymousDatasetTester::$ran2)->toBe([
        'test',
        123,
        true,
        'foo',
        456,
        false,
        'bar',
        789,
        null,
    ]);
});

action('an_action_missing_dataset_parameters', function (string $word) {
    // won't run
    AnonymousDatasetTester::$ran3[] = 'it actually ran';
})->dataset();

test('an action that expects dataset arguments but is missing a dataset parameter will fail')
    ->action('an_action_missing_dataset_parameters')
    ->with([
        'example 1' => ['abc', 123],
    ])
    ->throws('The `an_action_missing_dataset_parameters` action is missing dataset argument #2');
