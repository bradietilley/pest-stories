<?php

use BradieTilley\StoryBoard\Contracts\ExpectsThrows;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Config;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\RiskyTestError;
use PHPUnit\Framework\SkippedTestError;
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
    } catch (SkippedTestError $e) {
        expect($e->getMessage())->toBe($message);
    }
})->with([
    'no message' => '',
    'a message' => 'will work on later',
]);

test('a story can be marked as risky', function (string $message) {
    $story = Story::make('parent')
        ->risky($message)
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
    } catch (RiskyTestError $e) {
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
        ->risky('c')
        ->stories([
            Story::make('child'),
        ])
        ->storiesAll
        ->first();

    expect($story->getTestCaseShortcuts())->toBe([
        'incomplete' => 'a',
        'skipped' => 'b',
        'risky' => 'c',
    ]);
});

// if (! class_exists('PestStoryBoardTestFunctionWithThrows')) {
//     class PestStoryBoardTestFunctionWithThrows implements ExpectsThrows
//     {
//         public static ?array $throws = null;

//         public static ?array $throwsIf = null;

//         public function __construct(public string $name, public callable $callback)
//         {
//         }

//         public function throws(string $exception, ?string $exceptionMessage = null)
//         {
//             static::$throws = func_get_args();
//         }

//         public function throwsIf($condition, string $exception, ?string $exceptionMessage = null)
//         {
//             static::$throwsIf = func_get_args();
//         }

//         public function test(): void
//         {
//             if ($this->)
//         }

//         public function __destruct()
//         {
//             $this->test();
//         }
//     }

//     function newPestStoryBoardTestFunctionWithThrows(string $testName, callable $callback): PestStoryBoardTestFunctionWithThrows
//     {
//         return new PestStoryBoardTestFunctionWithThrows();
//     }
// }

// test('a story with throws will pass if the exception is thrown', function () {
//     Config::setAlias('test', 'newPestStoryBoardTestFunctionWithThrows');

//     $story = Story::make('test')
//         ->can(fn () => null)
//         ->throws(InvalidArgumentException::class)
//         ->action(fn () => throw new InvalidArgumentException('Woohoo'));

//     $story->test();

//     expect(PestStoryBoardTestFunctionWithThrows::$throws)->toBe([
//         InvalidArgumentException::class,
//     ]);

//     dd(PestStoryBoardTestFunctionWithThrows::$throws, PestStoryBoardTestFunctionWithThrows::$throwsIf);
// });
