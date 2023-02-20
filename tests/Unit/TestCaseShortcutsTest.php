<?php

use BradieTilley\StoryBoard\Contracts\ExpectsThrows;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Config;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\SkippedWithMessageException;
use Tests\TestCase;

test('a story can be marked as incomplete', function (string $message) {
    $story = Story::make('parent')
        ->incomplete($message)
        ->stories([
            Story::make('child'),
        ])
        ->storiesAll
        ->first();

    try {
        /** @var TestCase $this */
        $test = $this;
        $story->setTest($test)->run();

        $this->fail();
    } catch (IncompleteTestError $e) {
        expect($e->getMessage())->toBe($message);
    }
})->with([
    'no message' => '',
    'a message' => 'this is a wip',
]);

test('a story can be marked as skipped', function (string $message) {
    $story = Story::make('parent')
        ->skipped($message)
        ->stories([
            Story::make('child'),
        ])
        ->storiesAll
        ->first();

    try {
        /** @var TestCase $this */
        $test = $this;
        $story->setTest($test)->run();

        $this->fail();
    } catch (SkippedWithMessageException $e) {
        expect($e->getMessage())->toBe($message);
    }
})->with([
    'no message' => '',
    'a message' => 'will work on later',
]);

test('you can fetch the testcase shortcuts from a story', function () {
    $story = Story::make('parent')
        ->incomplete('a')
        ->skipped('b')
        ->stories([
            Story::make('child'),
        ])
        ->storiesAll
        ->first();

    expect($story->getTestCaseShortcuts())->toBe([
        'incomplete' => 'a',
        'skipped' => 'b',
    ]);
});

if (! class_exists(PestStoryBoardTestCall::class)) {
    class PestStoryBoardTestCall implements ExpectsThrows
    {
        public static ?string $exception = null;

        public static ?string $message = null;

        public function __construct(private Closure $callback)
        {
        }

        public function throws(string $exception, ?string $message = null): static
        {
            static::$exception = $exception;
            static::$message = $message;

            return $this;
        }

        public function throwsIf($condition, string $exception, ?string $message = null): static
        {
            if ($condition) {
                $this->throws($exception, $message);
            }

            return $this;
        }

        public static function flush(): void
        {
            static::$exception = null;
            static::$message = null;
        }
    }
}

if (! function_exists('pest_storyboard_test_function')) {
    function pest_storyboard_test_function(string $description, Closure $callback): PestStoryBoardTestCall
    {
        return new PestStoryBoardTestCall($callback);
    }
}

test('a story that is tested will have the expected exception passed to it', function (string $class, string $message = null, bool $success = false) {
    PestStoryBoardTestCall::flush();

    $story = Story::make('test')
        ->can(fn () => null)
        ->throws($class, $message)
        ->action(fn () => throw new InvalidArgumentException('Woohoo'));

    try {
        Config::setAlias('test', 'pest_storyboard_test_function');

        $story->test();
    } catch (Throwable $e) {
        //
    }

    expect(PestStoryBoardTestCall::$exception)->toBe($class);
    expect(PestStoryBoardTestCall::$message)->toBe($message);
})->with([
    'an exception with no message' => [
        'class' => InvalidArgumentException::class,
        'message' => null,
    ],
    'an exception with a message' => [
        'class' => JsonException::class,
        'message' => 'an example',
    ],
]);

test('a story that is tested will have the expected exception passed to it conditionally', function (string $class, string $message = null, bool $success = false) {
    PestStoryBoardTestCall::flush();

    $bool = true;

    $story = Story::make('test')
        ->can(fn () => null)
        ->throwsIf($bool, $class, $message)
        ->action(fn () => throw new InvalidArgumentException('Woohoo'));

    try {
        Config::setAlias('test', 'pest_storyboard_test_function');

        $story->test();
    } catch (Throwable $e) {
        //
    }

    expect(PestStoryBoardTestCall::$exception)->toBe($class);
    expect(PestStoryBoardTestCall::$message)->toBe($message);
})->with([
    'an exception with no message' => [
        'class' => InvalidArgumentException::class,
        'message' => null,
    ],
    'an exception with a message' => [
        'class' => JsonException::class,
        'message' => 'an example',
    ],
]);
