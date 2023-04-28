<?php

use BradieTilley\Stories\Concerns\Stories;
use BradieTilley\Stories\Exceptions\DataVariableUnavailableException;
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
        expect(dataset()->toArray())->toBe([
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

        expect(dataset()->toArray())->toBe([
            '1',
            '2',
            '3',
        ]);
    })
    ->with([
        'values' => ['a', 'b', 'c'],
    ]);

test('an exception is thrown when fetching a dataset index that does not exist')
    ->action(fn () => dataset()->get(0))
    ->throws(DataVariableUnavailableException::class, 'The data (variable `0`) is unavailable');

test('the dataset can be interacted with as an array')
    ->action(function () {
        $dataset = dataset();

        expect(isset($dataset[0]))->toBeTrue();
        expect(isset($dataset[1]))->toBeTrue();
        expect(isset($dataset[2]))->toBeTrue();
        expect(isset($dataset[3]))->toBeFalse();
        expect(isset($dataset['a']))->toBeFalse();

        expect($dataset[0])->toBe('a');
        expect($dataset[1])->toBe('b');
        expect($dataset[2])->toBe('c');

        $dataset[0] = '1';
        $dataset[1] = '2';
        $dataset[2] = '3';
        $dataset['a'] = '4';

        expect($dataset[0])->toBe('1');
        expect($dataset[1])->toBe('2');
        expect($dataset[2])->toBe('3');

        expect(isset($dataset['a']))->toBeTrue();
        expect($dataset['a'])->toBe('4');
        unset($dataset['a']);

        unset($dataset[2]);
        expect($dataset[2])->toBeNull();

        $dataset->merge([
            0 => 'new',
            'a' => 'new2',
        ]);

        expect($dataset->toArray())->toBe([
            0 => 'new',
            1 => '2',
            'a' => 'new2',
        ]);
    })
    ->with([
        'values' => ['a', 'b', 'c'],
    ]);

test('the dataset can be iterated')
    ->action(function () {
        $expectKeys = [0, 1, 2, 3, 4];
        $expectValues = ['a', 'b', 'c', 'd', 'e'];

        $actualKeys = [];
        $actualValues = [];

        foreach (dataset() as $key => $value) {
            $actualKeys[] = $key;
            $actualValues[] = $value;
        }

        expect($actualKeys)->toBe($expectKeys);
        expect($actualValues)->toBe($expectValues);
    })
    ->with([
        'values' => ['a', 'b', 'c', 'd', 'e'],
    ]);
