<?php

use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Helpers\StoryAliases;
use BradieTilley\Stories\Story;
use Pest\Matchers\Any;
use PHPUnit\Framework\Constraint\IsAnything;
use Tests\Mocks\PestStoriesMockExpectation;

beforeEach(function () {
    StoryAliases::setFunction('expect', 'pest_stories_mock_expect_function');
    PestStoriesMockExpectation::$calls = [];
    PestStoriesMockExpectation::$gets = [];
});

test('a story can have an expectation which will queue the expectation', function () {
    $story = story('test')
        ->action(fn (Story $story) => $story->set('something', [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]))
        ->expect('something')
        ->toBeArray()
        ->toHaveCount(3)
        ->toHaveKeys([
            'a',
            'b',
            'c',
        ])
        ->story();

    $expectations = collect($story->chain()->queue->items);
    // expect + toBeArray + toHaveCount + toHaveKeys
    expect($expectations)->toHaveCount(4);

    expect(PestStoriesMockExpectation::$calls)->toBe([]);

    $story->process();

    expect(PestStoriesMockExpectation::$calls)->toBe([
        [
            'toBeArray',
            [],
        ],
        [
            'toHaveCount',
            [
                3,
            ],
        ],
        [
            'toHaveKeys',
            [
                [
                    'a',
                    'b',
                    'c',
                ],
            ],
        ],
    ]);
});

test('all expectation methods', function () {
    $methodsToRun = [
        'toBe' => ['abc', 'custom-message'],
        'toBeEmpty' => ['custom-message'],
        'toBeTrue' => ['custom-message'],
        'toBeTruthy' => ['custom-message'],
        'toBeFalse' => ['custom-message'],
        'toBeFalsy' => ['custom-message'],
        'toBeGreaterThan' => [123, 'custom-message'],
        'toBeGreaterThanOrEqual' => [123, 'custom-message'],
        'toBeLessThan' => [123, 'custom-message'],
        'toBeLessThanOrEqual' => [123, 'custom-message'],
        'toContain' => ['a', 'b', 'c'],
        'toStartWith' => ['abc', 'custom-message'],
        'toEndWith' => ['def', 'custom-message'],
        'toHaveLength' => [123, 'custom-message'],
        'toHaveCount' => [456, 'custom-message'],
        'toHaveProperty' => ['aaa', new Any(), 'custom-message'],
        'toHaveProperties' => [['aaa', 'bbb', 'ccc'], 'custom-message'],
        'toHaveMethod' => ['someMethod', 'custom-message'],
        'toHaveMethods' => [['method1', 'method2'], 'custom-message'],
        'toEqual' => ['a value', 'custom-message'],
        'toEqualCanonicalizing' => ['abc', 'custom-message'],
        'toEqualWithDelta' => [123, 1.0, 'custom-message'],
        'toBeIn' => [['a', 'b', 'c'], 'custom-message'],
        'toBeInfinite' => ['custom-message'],
        'toBeInstanceOf' => [Story::class, 'custom-message'],
        'toBeArray' => ['custom-message'],
        'toBeBool' => ['custom-message'],
        'toBeCallable' => ['custom-message'],
        'toBeFloat' => ['custom-message'],
        'toBeInt' => ['custom-message'],
        'toBeIterable' => ['custom-message'],
        'toBeNumeric' => ['custom-message'],
        'toBeObject' => ['custom-message'],
        'toBeResource' => ['custom-message'],
        'toBeScalar' => ['custom-message'],
        'toBeString' => ['custom-message'],
        'toBeJson' => ['custom-message'],
        'toBeNan' => ['custom-message'],
        'toBeNull' => ['custom-message'],
        'toHaveKey' => ['a', new Any(), 'custom-message'],
        'toHaveKeys' => [['a', 'b', 'c'], 'custom-message'],
        'toBeDirectory' => ['custom-message'],
        'toBeReadableDirectory' => ['custom-message'],
        'toBeWritableDirectory' => ['custom-message'],
        'toBeFile' => ['custom-message'],
        'toBeReadableFile' => ['custom-message'],
        'toBeWritableFile' => ['custom-message'],
        'toMatchArray' => [['a' => 1], 'custom-message'],
        'toMatchObject' => [['a' => 1], 'custom-message'],
        'toMatch' => ['/regex/', 'custom-message'],
        'toMatchConstraint' => [new IsAnything(), 'custom-message'],
        'toContainOnlyInstancesOf' => [Story::class, 'custom-message'],
        'toThrow' => [StoryException::class, 'a custom message', 'custom-message'],
    ];

    $story = story();
    $expect = $story->expect('something');
    $total = 1; // starts with expect() which is 1

    foreach ($methodsToRun as $method => $args) {
        $total++;
        $result = $expect->{$method}(...$args);

        // returns static
        expect($result)->toBe($expect);

        // Can reference story still
        expect($result->story())->toBe($story);

        // Chain is of the expected length
        $chain = $story->chain()->queue->items;
        expect($chain)->toHaveCount($total);

        $last = end($chain);

        expect($last->name)->toBe($method);
        expect($last->arguments)->toBe($args);
    }
});

test('a story can have multiple expectations', function () {
    action('as_admin')->as(fn () => (object) [
        'id' => 0,
        'name' => 'Someone',
        'role' => 'admin',
    ])->for('user');

    action('create_invoice')->as(fn () => (object) [
        'id' => 0,
        'items' => collect([
            (object) [
                'item' => 'Test',
                'qty' => 1,
                'price' => 100,
            ],
            (object) [
                'item' => 'Test',
                'qty' => 5,
                'price' => 10,
            ],
            (object) [
                'item' => 'Test',
                'qty' => 2,
                'price' => 25,
            ],
        ]),
        'total' => 200,
    ])->for('invoice');

    $original = story('an admin can create an invoice')
        ->action('as_admin')
        ->action('create_invoice');

    $story = $original
        ->expect('user')
        ->toBeObject()
        ->toHaveKeys([
            'id',
            'name',
            'role',
        ])
        ->expect('invoice')
        ->toBeObject()
        ->total->toBe(200)
        ->items->toHaveCount(3)
        ->items->get(0)->qty->toBe(1)
        ->items->get(0)->price->toBe(100)
        ->items->get(1)->qty->toBe(5)
        ->items->get(1)->price->toBe(10)
        ->items->get(2)->qty->toBe(2)
        ->items->get(2)->price->toBe(25)
        ->story();

    expect($story)->toBe($original);

    $actual = $original->chain()->queue->toArray();
    $actual = json_encode($actual);

    $expect = '[{"type":"function","name":"pest_stories_mock_expect_function","arguments":["user"],"object":null},{"type":"method","name":"toBeObject","arguments":[],"object":null},{"type":"method","name":"toHaveKeys","arguments":[["id","name","role"]],"object":null},{"type":"function","name":"pest_stories_mock_expect_function","arguments":["invoice"],"object":null},{"type":"method","name":"toBeObject","arguments":[],"object":null},{"type":"property","name":"total","arguments":[],"object":null},{"type":"method","name":"toBe","arguments":[200],"object":null},{"type":"property","name":"items","arguments":[],"object":null},{"type":"method","name":"toHaveCount","arguments":[3],"object":null},{"type":"property","name":"items","arguments":[],"object":null},{"type":"method","name":"get","arguments":[0],"object":null},{"type":"property","name":"qty","arguments":[],"object":null},{"type":"method","name":"toBe","arguments":[1],"object":null},{"type":"property","name":"items","arguments":[],"object":null},{"type":"method","name":"get","arguments":[0],"object":null},{"type":"property","name":"price","arguments":[],"object":null},{"type":"method","name":"toBe","arguments":[100],"object":null},{"type":"property","name":"items","arguments":[],"object":null},{"type":"method","name":"get","arguments":[1],"object":null},{"type":"property","name":"qty","arguments":[],"object":null},{"type":"method","name":"toBe","arguments":[5],"object":null},{"type":"property","name":"items","arguments":[],"object":null},{"type":"method","name":"get","arguments":[1],"object":null},{"type":"property","name":"price","arguments":[],"object":null},{"type":"method","name":"toBe","arguments":[10],"object":null},{"type":"property","name":"items","arguments":[],"object":null},{"type":"method","name":"get","arguments":[2],"object":null},{"type":"property","name":"qty","arguments":[],"object":null},{"type":"method","name":"toBe","arguments":[2],"object":null},{"type":"property","name":"items","arguments":[],"object":null},{"type":"method","name":"get","arguments":[2],"object":null},{"type":"property","name":"price","arguments":[],"object":null},{"type":"method","name":"toBe","arguments":[25],"object":null}]';

    expect($actual)->toBe($expect);
});

test('a story expectation chain may fetch properties', function () {
    $actual = (object) [
        'abc' => '123',
    ];

    story('test')
        ->action(fn (Story $story) => $story->set('something', $actual))
        ->expect('something')
        ->toHaveKeys([
            'abc',
        ])
        ->abc
        ->toBe('123')
        ->story()
        ->process();

    expect(PestStoriesMockExpectation::$calls)->toBe([
        [
            'toHaveKeys',
            [
                ['abc'],
            ],
        ],
        [
            'toBe',
            [
                '123',
            ],
        ],
    ]);

    expect(PestStoriesMockExpectation::$gets)->toBe([
        'abc',
    ]);
});

test('a chain can be added to a parent story\'s stories method', function () {
    story('')
        ->action(fn () => null)
        ->stories([
            story()->expect('a')->toBe('1'),
            story()->stories(
                story()->expect('b')->toBe('c'),
            ),
        ]);
})->throwsNoExceptions();

test('a story expectations can be inherited from the parent story', function () {
    $story = story('test parent story')
        ->action(fn () => '123', for: 'a')
        ->action(fn () => '456', for: 'b')
        ->set('a', '123')
        ->set('b', '456')
        ->expect('a')
        ->toHaveLength(3)
        ->expect('b')
        ->toHaveLength(3)
        ->stories([
            story('child 1')->expect('a')->toBe('***'),
            story('child 2')->expect('a')->toBe('123')->stories([
                story('child 3')->expect('b')->toBe('***'),
                story('child 4')->expect('b')->toBe('456'),
            ]),
        ]);

    $actual = $story->flattenStories()->map(fn (Story $story) => json_encode($story->chain()->queue->items))->toArray();
    $expect = [
        '[{"type":"function","name":"pest_stories_mock_expect_function","arguments":["a"],"object":null},{"type":"method","name":"toHaveLength","arguments":[3],"object":null},{"type":"function","name":"pest_stories_mock_expect_function","arguments":["b"],"object":null},{"type":"method","name":"toHaveLength","arguments":[3],"object":null},{"type":"function","name":"pest_stories_mock_expect_function","arguments":["a"],"object":null},{"type":"method","name":"toBe","arguments":["***"],"object":null}]',
        '[{"type":"function","name":"pest_stories_mock_expect_function","arguments":["a"],"object":null},{"type":"method","name":"toHaveLength","arguments":[3],"object":null},{"type":"function","name":"pest_stories_mock_expect_function","arguments":["b"],"object":null},{"type":"method","name":"toHaveLength","arguments":[3],"object":null},{"type":"function","name":"pest_stories_mock_expect_function","arguments":["a"],"object":null},{"type":"method","name":"toBe","arguments":["123"],"object":null},{"type":"function","name":"pest_stories_mock_expect_function","arguments":["b"],"object":null},{"type":"method","name":"toBe","arguments":["***"],"object":null}]',
        '[{"type":"function","name":"pest_stories_mock_expect_function","arguments":["a"],"object":null},{"type":"method","name":"toHaveLength","arguments":[3],"object":null},{"type":"function","name":"pest_stories_mock_expect_function","arguments":["b"],"object":null},{"type":"method","name":"toHaveLength","arguments":[3],"object":null},{"type":"function","name":"pest_stories_mock_expect_function","arguments":["a"],"object":null},{"type":"method","name":"toBe","arguments":["123"],"object":null},{"type":"function","name":"pest_stories_mock_expect_function","arguments":["b"],"object":null},{"type":"method","name":"toBe","arguments":["456"],"object":null}]',
    ];

    expect($actual)->toBe($expect);
});
