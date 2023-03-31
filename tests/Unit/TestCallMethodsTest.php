<?php

use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Helpers\StoryAliases;
use BradieTilley\Stories\Story;

test('a story can mark the test as todo', function () {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');

    /**
     * Create non-todo story
     */
    $story = story('todo story');

    /**
     * Expect that the TestCall todo() is not run
     */
    $call = $story->test();
    expect($call->calls)->toBe([]);

    /**
     * Now mark it as todo
     */
    $story->todo();

    /**
     * Expect that the TestCall todo() is run once test() is run
     */
    $call = $story->test();
    expect($call->calls)->toBe([
        [
            'todo',
            [],
        ],
    ]);
});

test('a story can mark the test as skipped', function () {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');

    $story = story('skipped story');
    $call = $story->test();
    expect($call->calls)->toBe([]);

    $story = story('skipped story');
    $story->skip('skipped because');
    $call = $story->test();
    expect($call->calls)->toBe([
        [
            'skip',
            [
                'skipped because',
            ],
        ],
    ]);
});

test('a story can mark the test as skipped at a parent level', function () {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');

    $story = story('something not working')
        ->stories([
            story('a')->skip('not complete 1'),
            story('b')->skip()->stories([
                story('not complete 2a'),
                story('not complete 2b'),
                story('not complete 2c')->stories([
                    story('i'),
                ]),
            ]),
        ]);

    $call = $story->test();

    // Sanity check
    expect($call->dataset)->toBeArray()->toHaveCount(4)->toHaveKeys([
        'a',
        'b not complete 2a',
        'b not complete 2b',
        'b not complete 2c i',
    ]);

    // All 4 should be incomplete
    $all = [];

    foreach ($call->dataset as $args) {
        $story = $args[0];
        /** @var Story $story */
        $call = $story->test();

        $all[] = $call->calls;
    }

    $expected = [
        [
            [
                'skip',
                [
                    'not complete 1',
                ],
            ],
        ],
        [
            [
                'skip',
                [],
            ],
        ],
        [
            [
                'skip',
                [],
            ],
        ],
        [
            [
                'skip',
                [],
            ],
        ],
    ];

    expect($all)->toBe($expected);
});

test('all TestCall proxies pass the exact same arguments to the TestCall object', function (string $method, array $arguments) {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');

    $story = story('test story');
    $story->{$method}(...$arguments);
    $testCall = $story->test();

    $expect = [
        [
            $method,
            $arguments,
        ],
    ];

    $actual = $testCall->calls;

    expect($actual)->toBe($expect);
})->with([
    'throws' => [
        'method' => 'throws',
        'arguments' => [
            'TestExceptionClass', 'TestExceptionMessage', 404,
        ],
    ],
    'throwsIf' => [
        'method' => 'throwsIf',
        'arguments' => [
            false, 'TestExceptionClass', 'TestExceptionMessage', 404,
        ],
    ],
    'depends' => [
        'method' => 'depends',
        'arguments' => [
            'depend 1', 'depend 2',
        ],
    ],
    'group' => [
        'method' => 'group',
        'arguments' => [
            'group 1', 'group 2',
        ],
    ],
    'skip' => [
        'method' => 'skip',
        'arguments' => [
            false, 'message',
        ],
    ],
    'todo' => [
        'method' => 'todo',
        'arguments' => [

        ],
    ],
    'covers' => [
        'method' => 'covers',
        'arguments' => [
            'Class1', 'function_1',
        ],
    ],
    'coversClass' => [
        'method' => 'coversClass',
        'arguments' => [
            'Class1', 'Class2',
        ],
    ],
    'coversFunction' => [
        'method' => 'coversFunction',
        'arguments' => [
            'function 1', 'function 2',
        ],
    ],
    'coversNothing' => [
        'method' => 'coversNothing',
        'arguments' => [

        ],
    ],
    'throwsNoExceptions' => [
        'method' => 'throwsNoExceptions',
        'arguments' => [

        ],
    ],
]);
