<?php

use function BradieTilley\StoryBoard\debug;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;
use BradieTilley\StoryBoard\Story\Config;

test('debug information is available after a story is run', function () {
    Action::make('do_something', fn () => 'test', 'something');

    $story = Story::make()
        ->action('do_something')
        ->can(fn () => null);

    $story->assignDebugContainer()->run();

    $debug = debug()->prepareForDumping()->values();

    expect($debug)->toContain('Story created');
    expect($debug)->toContain('Test::run() start');
    expect($debug)->toContain('Test::run() success');
});

if (! class_exists(PestStoryBoardDumpFunction::class)) {
    class PestStoryBoardDumpFunction
    {
        protected static array $all = [];

        public static function flush(): void
        {
            static::$all = [];
        }

        public static function push(): void
        {
            foreach (func_get_args() as $arg) {
                static::$all[] = $arg;
            }
        }

        public static function all(): array
        {
            return static::$all;
        }
    }
}

if (! function_exists('pest_storyboard_test_dump_fn')) {
    function pest_storyboard_test_dump_fn()
    {
        PestStoryBoardDumpFunction::push(...func_get_args());
    }
}

test('debug information is printed when debug function is called', function (string $how) {
    $exception = new InvalidArgumentException('Test');

    $story = Story::make()
        ->action(fn () => throw $exception)
        ->can(fn () => null);

    if ($how === 'method') {
        $story->debug();
    }

    if ($how === 'config') {
        Config::set('debug.enabled', true);
    }

    Config::setAlias('dump', 'pest_storyboard_test_dump_fn');
    PestStoryBoardDumpFunction::flush();

    try {
        $story->assignDebugContainer()->run();
    } catch (Throwable $e) {
        //
    }

    $all = PestStoryBoardDumpFunction::all();

    // Only called once
    expect($all)->toBeArray()->toHaveCount(1)->toHaveKey(0);

    // dump args
    $all = collect($all[0]);

    // assert count?
    // $all->count();

    $all->keys()->each(
        fn (string $key) => expect($key)->toMatch('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}.\d{6}: [a-zA-Z0-9]{8}\] (debug|info|error)/')
    );

    $expect = [
        0 => 'Story created',
        1 => 'Test::run() start',
        2 => 'Timeout disabled, running story directly',
        3 => 'Running test',
        4 => 'Inheriting from parent stories',
        5 => 'Checking if `inherited` has already run: First time running',
        6 => 'Checking if `register` has already run: First time running',
        7 => 'Running Action::register callback with args:',
        8 => [
            'story' => $story,
            'test' => null,
            'can' => true,
            'user' => null,
            'result' => $story->getResult(),
        ],
        9 => 'Checking if `boot` has already run: First time running',
        10 => 'Running Story::before callback with args:',
        11 => [
            'story' => $story,
            'test' => null,
            'can' => true,
            'user' => null,
            'result' => null,
        ],
        12 => 'Repeater disabled: run this once',
        13 => 'Running Action::boot callback with args:',
        14 => [
            'story' => $story,
            'test' => null,
            'can' => true,
            'user' => null,
            'result' => null,
        ],
        15 => 'Running Action::generator callback with args:',
        16 => [
            'story' => $story,
            'test' => null,
            'can' => true,
            'user' => null,
            'result' => null,
        ],
        17 => 'Failed to boot actions with error',
        18 => $exception,
    ];

    expect($all->values()->all())->toBe($expect);
})->with([
    'config debug.enabled set to true' => 'config',
    'chained ->debug method' => 'method',

]);
