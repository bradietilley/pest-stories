<?php

use BradieTilley\Stories\Concerns\Stories;
use function BradieTilley\Stories\Helpers\dataset;

uses(Stories::class);

class DatasetClassTestCounter
{
    public static $counter = 0;
}

test('a dataset value can be fetched')
    ->action(function () {
        $dataset = dataset();

        $expect = [
            ['a', 1],
            ['b', 2],
            ['c', 3],
        ][DatasetClassTestCounter::$counter];

        $actual = $dataset->all();
        expect($actual)->tobe($expect);

        expect(dataset(0))->toBe($expect[0]);
        expect(dataset(1))->toBe($expect[1]);

        DatasetClassTestCounter::$counter++;
    })
    ->with([
        'a1' => ['a', 1],
        'b2' => ['b', 2],
        'c3' => ['c', 3],
    ]);

test('a dataset value can be overwritten on the fly')
    ->action(function () {
        expect(dataset()->all())->toBe([
            'a',
            'b',
            'c',
        ]);

        expect(dataset(0))->toBe('a');
        dataset(0, '1');
        expect(dataset(0))->toBe('1');

        expect(dataset(1))->toBe('b');
        dataset(1, '2');
        expect(dataset(1))->toBe('2');

        expect(dataset(2))->toBe('c');
        dataset(2, '3');
        expect(dataset(2))->toBe('3');

        expect(dataset()->all())->toBe([
            '1',
            '2',
            '3',
        ]);
    })
    ->with([
        'values' => ['a', 'b', 'c'],
    ]);
