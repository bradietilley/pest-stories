<?php

use BradieTilley\Stories\Helpers\VariableNaming;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Tests\Mocks\TestEnum;

test('variables of varying types can all be converted stringified', function () {
    expect(VariableNaming::stringify('abc'))->toBe('abc');
    expect(VariableNaming::stringify(123))->toBe('123');
    expect(VariableNaming::stringify(123.45))->toBe('123.45');
    expect(VariableNaming::stringify(false))->toBe('false');
    expect(VariableNaming::stringify(true))->toBe('true');
    expect(VariableNaming::stringify(null))->toBe('null');
    expect(VariableNaming::stringify([]))->toBe('[]');
    expect(VariableNaming::stringify([1, 2]))->toBe('[1,2]');
    expect(VariableNaming::stringify(['abc' => 123]))->toBe('{"abc":123}');

    $stringable = new class implements Stringable
    {
        public function __toString(): string
        {
            return 'string value';
        }
    };
    expect(VariableNaming::stringify($stringable))->toBe('string value');

    $arrayable = new class implements Arrayable
    {
        public function toArray(): array
        {
            return ['def' => 456];
        }
    };
    expect(VariableNaming::stringify($arrayable))->toBe('{"def":456}');

    $jsonable = new class implements Jsonable
    {
        public function toJson($options = 0): string
        {
            return json_encode(['abc' => 789]);
        }
    };
    expect(VariableNaming::stringify($jsonable))->toBe('{"abc":789}');

    expect(VariableNaming::stringify(TestEnum::TWO))->toBe('two');

    expect(VariableNaming::stringify(new class()
    {
    }))->toBe('???');
});
