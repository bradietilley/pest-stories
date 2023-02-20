<?php

use BradieTilley\StoryBoard\Exceptions\AliasNotFoundException;
use BradieTilley\StoryBoard\Exceptions\InvalidConfigurationException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Config;
use Illuminate\Support\Facades\Config as FacadesConfig;

function getValueTypes(string $expect): array
{
    $configValueTypes = [
        'integer' => 123,
        'float' => 123.45,
        'boolean' => true,
        'string' => 'test',
        'array' => ['test'],
        'object' => (object) ['test'],
        'resource' => fopen(__FILE__, 'r'),
    ];

    return collect($configValueTypes)
        ->map(fn ($value, string $key) => [
            'type' => $key,
            'value' => $value,
        ])
        ->keyBy('type')
        ->forget($expect)
        ->toArray();
}

test('can fetch PSB config', function () {
    expect(FacadesConfig::get('storyboard'))->toBeArray()->toHaveKeys(['datasets', 'naming']);
});

test('an empty alias class will throw the expected exception', function () {
    Config::set('aliases.story', '');

    Config::getAliasClass('story', Story::class);
})->throws(AliasNotFoundException::class, 'The `story` alias config was not found');

test('a non existent alias class will throw the expected exception', function () {
    Config::set('aliases.story', 'Class\Doesnt\Exist');

    Config::getAliasClass('story', Story::class);
})->throws(AliasNotFoundException::class, 'The `story` alias class `Class\Doesnt\Exist` was not found');

if (! class_exists(PestStoryBoardTestStoryClass::class)) {
    class PestStoryBoardTestStoryClass
    {
    }
}

test('an invalid alias class will throw the expected exception', function () {
    Config::set('aliases.story', 'PestStoryBoardTestStoryClass');

    Config::getAliasClass('story', Story::class);
})->throws(AliasNotFoundException::class, 'The `story` alias class `PestStoryBoardTestStoryClass` is not a subclass of `BradieTilley\StoryBoard\Story`');

test('an empty alias function will throw the expected exception', function () {
    Config::set('aliases.test', '');

    Config::getAliasFunction('test');
})->throws(AliasNotFoundException::class, 'The `test` alias config was not found');

test('a non existent alias function will throw the expected exception', function () {
    Config::set('aliases.test', 'pest_test_function_does_not_exist');

    Config::getAliasFunction('test');
})->throws(AliasNotFoundException::class, 'The `test` alias function `pest_test_function_does_not_exist` was not found');

test('a setting value that is expected to be a string will accept a string', function () {
    Config::set('test.key.here', $expect = 'prefix');
    $actual = Config::getString('test.key.here', 'default');

    expect($actual)->toBe($expect);
});

test('a setting value that is expected to be a string will fail on a non-string', function (string $type, mixed $expect) {
    Config::set('test.key.here', $expect);

    try {
        Config::getString('test.key.here', 'default');

        $this->fail();
    } catch (InvalidConfigurationException $e) {
        expect($e->getMessage())
            ->toBe("Invalid config: The `test.key.here` key must be a string; {$type} found.");
    }
})->with(getValueTypes('string'));

test('a setting value that is expected to be a integer will accept a integer', function () {
    Config::set('test.key.here', $expect = 1);
    $actual = Config::getInteger('test.key.here', 2);

    expect($actual)->toBe($expect);
});

test('a setting value that is expected to be a integer will fail on a non-integer', function (string $type, mixed $expect) {
    Config::set('test.key.here', $expect);

    try {
        Config::getInteger('test.key.here', 1);

        $this->fail();
    } catch (InvalidConfigurationException $e) {
        expect($e->getMessage())
            ->toBe("Invalid config: The `test.key.here` key must be a integer; {$type} found.");
    }
})->with(getValueTypes('integer'));

test('a setting value that is expected to be a float will accept a float', function () {
    Config::set('test.key.here', $expect = 1.2);
    $actual = Config::getFloat('test.key.here', 2.2);

    expect($actual)->toBe($expect);
});

test('a setting value that is expected to be a float will fail on a non-float', function (string $type, mixed $expect) {
    Config::set('test.key.here', $expect);

    try {
        Config::getFloat('test.key.here', 1.23);

        $this->fail();
    } catch (InvalidConfigurationException $e) {
        expect($e->getMessage())
            ->toBe("Invalid config: The `test.key.here` key must be a float; {$type} found.");
    }
})->with(getValueTypes('float'));

test('a setting value that is expected to be a array will accept a array', function () {
    Config::set('test.key.here', $expect = ['nice']);
    $actual = Config::getArray('test.key.here', ['default']);

    expect($actual)->toBe($expect);
});

test('a setting value that is expected to be a array will fail on a non-array', function (string $type, mixed $expect) {
    Config::set('test.key.here', $expect);

    try {
        Config::getArray('test.key.here', []);

        $this->fail();
    } catch (InvalidConfigurationException $e) {
        expect($e->getMessage())
            ->toBe("Invalid config: The `test.key.here` key must be a array; {$type} found.");
    }
})->with(getValueTypes('array'));

test('a setting value that is expected to be a boolean will accept a boolean', function () {
    Config::set('test.key.here', $expect = false);
    $actual = Config::getBoolean('test.key.here', true);

    expect($actual)->toBe($expect);
});

test('a setting value that is expected to be a boolean will fail on a non-boolean', function (string $type, mixed $expect) {
    Config::set('test.key.here', $expect);

    try {
        Config::getBoolean('test.key.here', false);

        $this->fail();
    } catch (InvalidConfigurationException $e) {
        expect($e->getMessage())
            ->toBe("Invalid config: The `test.key.here` key must be a boolean; {$type} found.");
    }
})->with(getValueTypes('boolean'));
