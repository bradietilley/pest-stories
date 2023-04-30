<?php

use BradieTilley\Stories\Concerns\Stories;
use function BradieTilley\Stories\Helpers\dataset;
use function BradieTilley\Stories\Helpers\story;

uses(Stories::class);

class TestStoryDatasetCounter
{
    public static int $index = 0;

    public static int $index2 = 0;

    public static int $index3 = 0;

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

test('an action used by a story can accept dataset arguments by their respective string names')
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

test('an action used by a story can fetch dataset arguments by their integer index')
    ->action(function () {
        story()->merge([
            'a' => dataset(0),
            'b' => dataset(1),
            'c' => dataset(2),
        ]);
    })
    ->action(function (int $a, int $b, int $c) {
        TestStoryDatasetCounter::$index3++;

        $expect = match (TestStoryDatasetCounter::$index3) {
            1 => TestStoryDatasetCounter::DATASET_ONE,
            2 => TestStoryDatasetCounter::DATASET_TWO,
            3 => TestStoryDatasetCounter::DATASET_THREE,
            default => throw new \Exception('Fail'),
        };
        $expect = array_values($expect);

        $actual = [
            $a,
            $b,
            $c,
        ];

        expect($actual)->toBe($expect);
    })
    ->with([
        array_values(TestStoryDatasetCounter::DATASET_ONE),
        array_values(TestStoryDatasetCounter::DATASET_TWO),
        array_values(TestStoryDatasetCounter::DATASET_THREE),
    ]);

test('you can map integer dataset into story variables')
    ->mapDataset([
        'word',
        'number',
    ])
    ->action(function (string $word, int $number) {
        expect($word)->toBe('abc');
        expect($number)->toBe(123);
    })
    ->with([
        ['abc', 123],
    ]);
