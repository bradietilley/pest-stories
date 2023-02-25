<?php

use function BradieTilley\StoryBoard\debug;
use function BradieTilley\StoryBoard\error;
use function BradieTilley\StoryBoard\info;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;
use BradieTilley\StoryBoard\Story\Config;
use BradieTilley\StoryBoard\Story\DebugContainer;
use BradieTilley\StoryBoard\Testing\Timer\Timer;
use BradieTilley\StoryBoard\Testing\Timer\TimerUpException;
use function BradieTilley\StoryBoard\warning;
use Illuminate\Support\Str;

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

test('debug information is printed when debug function is called', function (string $how, Throwable $exception, string $error) {
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
        5 => 'Checking if `inherit` has already run: First time running',
        6 => 'Checking if `register` has already run: First time running',
        7 => 'Running Action::register callback with args:',
        8 => [
            'story' => $story,
            'test' => null,
            'expectation' => true,
            'user' => null,
            'result' => $story->getResult(),
        ],
        9 => 'Checking if `boot` has already run: First time running',
        10 => 'Running Story::before callback with args:',
        11 => [
            'story' => $story,
            'test' => null,
            'expectation' => true,
            'user' => null,
            'result' => null,
        ],
        12 => 'Repeater disabled: run this once',
        13 => 'Running Action::boot callback with args:',
        14 => [
            'story' => $story,
            'test' => null,
            'expectation' => true,
            'user' => null,
            'result' => null,
        ],
        15 => 'Running Action::generator callback with args:',
        16 => [
            'story' => $story,
            'test' => null,
            'expectation' => true,
            'user' => null,
            'result' => null,
        ],
        17 => 'Failed to boot actions with error',
        18 => $exception,
        19 => $error,
        20 => $exception,
    ];

    expect($all->values()->all())->toBe($expect);
})->with([
    'standard exception config debug.enabled set to true' => [
        'how' => 'config',
        'exception' => new InvalidArgumentException('Test'),
        'error' => 'Test::run() unexpected error',
    ],
    'standard exception chained ->debug method' => [
        'how' => 'method',
        'exception' => new InvalidArgumentException('Test'),
        'error' => 'Test::run() unexpected error',
    ],
    'timer up exception config debug.enabled set to true' => [
        'how' => 'config',
        'exception' => new TimerUpException(Timer::make(fn () => null)),
        'error' => 'Test::run() timeout reached',
    ],
    'timer up exception chained ->debug method' => [
        'how' => 'method',
        'exception' => new TimerUpException(Timer::make(fn () => null)),
        'error' => 'Test::run() timeout reached',
    ],
]);

test('debug information is printed depending on the configured debug levels', function (
    ?string $configLevel = null,
    ?string $methodLevel = null,
    ?string $expectLevel = null,
) {
    $exception = new InvalidArgumentException('Test');

    $story = Story::make()
        ->action(fn () => debug('This is a test debug'))
        ->action(fn () => info('This is a test info'))
        ->action(fn () => warning('This is a test warning'))
        ->action(fn () => error('This is a test error'))
        ->action(fn () => throw $exception)
        ->can(fn () => null);

    if ($methodLevel) {
        $story->debug($methodLevel);
    }

    if ($configLevel) {
        Config::set('debug.enabled', true);
        Config::Set('debug.level', $configLevel);
    }

    Config::setAlias('dump', 'pest_storyboard_test_dump_fn');
    PestStoryBoardDumpFunction::flush();

    try {
        $story->assignDebugContainer()->run();
    } catch (Throwable $e) {
        //
    }

    $all = PestStoryBoardDumpFunction::all();

    if ($expectLevel === null) {
        expect($all)->toHaveCount(0);

        return;
    }

    // Only called once
    expect($all)->toBeArray()->toHaveCount(1)->toHaveKey(0);

    // dump args
    $all = collect($all[0]);

    $levelsFound = $all->keys()
        ->map(fn (string $key) => (string) Str::afterLast($key, '] '))
        ->unique()
        ->values();

    if ($expectLevel === 'debug') {
        expect($levelsFound)
            ->toContain(
                'error',
                'warning',
                'info',
                'debug',
            );
    }

    if ($expectLevel === 'info') {
        expect($levelsFound)
            ->toContain(
                'error',
                'warning',
                'info',
            )
            ->not()->toContain(
                'debug',
            );
    }

    if ($expectLevel === 'warning') {
        expect($levelsFound)
            ->toContain(
                'error',
                'warning',
            )
            ->not()->toContain(
                'debug',
                'info',
            );
    }

    if ($expectLevel === 'error') {
        expect($levelsFound)
            ->toContain(
                'error',
            )
            ->not()->toContain(
                'debug',
                'info',
                'warning',
            );
    }
})->with([
    'config=null, method=null' => [
        'config' => null,
        'method' => null,
        'expect' => null,
    ],
    'config=null, method=debug' => [
        'config' => null,
        'method' => 'debug',
        'expect' => 'debug',
    ],
    'config=null, method=info' => [
        'config' => null,
        'method' => 'info',
        'expect' => 'info',
    ],
    'config=null, method=warning' => [
        'config' => null,
        'method' => 'warning',
        'expect' => 'warning',
    ],
    'config=null, method=error' => [
        'config' => null,
        'method' => 'error',
        'expect' => 'error',
    ],
    'config=debug, method=null' => [
        'config' => 'debug',
        'method' => null,
        'expect' => 'debug',
    ],
    'config=debug, method=debug' => [
        'config' => 'debug',
        'method' => 'debug',
        'expect' => 'debug',
    ],
    'config=debug, method=info' => [
        'config' => 'debug',
        'method' => 'info',
        'expect' => 'info',
    ],
    'config=debug, method=warning' => [
        'config' => 'debug',
        'method' => 'warning',
        'expect' => 'warning',
    ],
    'config=debug, method=error' => [
        'config' => 'debug',
        'method' => 'error',
        'expect' => 'error',
    ],
]);

test('can flush DebugContainer', function () {
    $story = Story::make('test')->can(fn () => null)->action(fn () => null);

    DebugContainer::swap($instance = $story->getDebugContainer());

    expect(DebugContainer::instance())->toBe($instance);

    DebugContainer::flush();

    expect(DebugContainer::instance())->not()->toBe($instance);
});
