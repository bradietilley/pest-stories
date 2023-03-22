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
    expect($call->testCallProxies())->toBe([]);

    /**
     * Now mark it as todo
     */
    $story->todo();

    /**
     * Expect that the TestCall todo() is run
     */
    $call = $story->test();
    expect($call->testCallProxies())->toBe([
        'todo' => [
            [],
        ],
    ]);
});

test('a story can mark the test as skipped', function () {
    StoryAliases::setFunction('test', 'pest_stories_mock_test_function');

    $story = story('skipped story');
    $call = $story->test();
    expect($call->testCallProxies())->toBe([]);

    $story = story('skipped story');
    $story->skip('skipped because');
    $call = $story->test();
    expect($call->testCallProxies())->toBe([
        'skip' => [
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

        $all[] = $call->testCallProxies();
    }

    $expected = [
        [
            'skip' => [
                [
                    'not complete 1',
                ],
            ],
        ],
        [
            'skip' => [
                [],
            ],
        ],
        [
            'skip' => [
                [],
            ],
        ],
        [
            'skip' => [
                [],
            ],
        ],
    ];

    expect($all)->toBe($expected);
});
