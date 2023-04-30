<?php

use BradieTilley\Stories\Concerns\Stories;
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\dataset;
use BradieTilley\Stories\Story;

uses(Stories::class);

class AnonymousDatasetTester
{
    public static array $ran = [];

    public static array $ran2 = [];

    public static array $ran3 = [];

    public static array $ran4 = [];
}

action('do_something_with_datasets')->as(function (string $word, Story $story) {
    AnonymousDatasetTester::$ran[] = $word;

    expect($word)->toBeIn([
        'test',
        'foo',
        'bar',
    ]);
})->dataset();

action('do_something_in_general')->as(function (string $word, Story $story) {
    AnonymousDatasetTester::$ran4[] = $word;

    expect($word)->toBeIn([
        '111',
        '222',
        '333',
    ]);
});

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

action('an_action_missing_dataset_parameters', function (string $word) {
    // won't run
    AnonymousDatasetTester::$ran3[] = 'it actually ran';
})->dataset();

test('a normal action can be temporarily assigned the dataset using action with the dataset flag')
    ->action('do_something_in_general', dataset: true)
    ->with([
        '111',
        '222',
        '333',
    ]);

test('after a normal action is temporarily assigned the dataaset')
    ->action(function () {
        expect(AnonymousDatasetTester::$ran4)->toBe([
            '111',
            '222',
            '333',
        ]);

        $requiresDataset = action()->fetch('do_something_in_general')->requiresDataset();
        expect($requiresDataset)->toBeFalse();
    });

class TestStoryDatasetCounter
{
    public static int $index = 0;

    public static int $index2 = 0;

    public const DATASET_ONE = [
        'abc' => 111,
        'def' => 222,
        'ghi' => 333,
    ];

    public const DATASET_TWO = [
        'abc' => 444,
        'def' => 555,
        'ghi' => 666,
    ];

    public const DATASET_THREE = [
        'abc' => 777,
        'def' => 888,
        'ghi' => 999,
    ];
}

test('a test may be given a dataset with keyed values')
    ->action(function () {
        TestStoryDatasetCounter::$index++;

        expect(dataset()->has('abc'))->toBeTrue();
        expect(dataset()->has('def'))->toBeTrue();
        expect(dataset()->has('ghi'))->toBeTrue();
        expect(dataset()->has('jkl'))->toBeFalse();

        $expect = match (TestStoryDatasetCounter::$index) {
            1 => TestStoryDatasetCounter::DATASET_ONE,
            2 => TestStoryDatasetCounter::DATASET_TWO,
            3 => TestStoryDatasetCounter::DATASET_THREE,
            default => throw new \Exception('Fail'),
        };

        expect(dataset()->all())->toBe($expect);

        $actualAbc = dataset('abc');
        $expectAbc = $expect['abc'];

        expect($actualAbc)->toBe($expectAbc);
    })
    ->with([
        'custom message 1' => TestStoryDatasetCounter::DATASET_ONE,
        'custom message 2' => TestStoryDatasetCounter::DATASET_TWO,
        'custom message 3' => TestStoryDatasetCounter::DATASET_THREE,
    ]);

test('an action used by a story can accept dataset arguments by their respective names')
    ->action(function (int $abc, int $def, int $ghi) {
        TestStoryDatasetCounter::$index2++;

        expect(dataset()->has('abc'))->toBeTrue();
        expect(dataset()->has('def'))->toBeTrue();
        expect(dataset()->has('ghi'))->toBeTrue();
        expect(dataset()->has('jkl'))->toBeFalse();

        $expect = match (TestStoryDatasetCounter::$index2) {
            1 => TestStoryDatasetCounter::DATASET_ONE,
            2 => TestStoryDatasetCounter::DATASET_TWO,
            3 => TestStoryDatasetCounter::DATASET_THREE,
            default => throw new \Exception('Fail'),
        };

        expect([
            $abc,
            $def,
            $ghi,
        ])->toBe(array_values($expect));
    })
    ->with([
        'custom message 1' => TestStoryDatasetCounter::DATASET_ONE,
        'custom message 2' => TestStoryDatasetCounter::DATASET_TWO,
        'custom message 3' => TestStoryDatasetCounter::DATASET_THREE,
    ]);
