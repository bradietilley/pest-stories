<?php

use BradieTilley\StoryBoard\Exceptions\AliasNotFoundException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Config;
use Illuminate\Support\Collection;

if (! class_exists('PestStoryBoardTestFunction')) {
    class PestStoryBoardTestFunction
    {
        protected static ?Collection $testExecutions = null;

        public static function testExecutions(): Collection
        {
            return self::$testExecutions ??= Collection::make();
        }

        public static function flush(): void
        {
            self::$testExecutions = Collection::make();
        }

        public function __construct(string $description, Closure $callback)
        {
            self::testExecutions()->push([
                'type' => 'function',
                'value' => [
                    'description' => $description,
                    'callback' => $callback,
                ],
            ]);
        }

        public function with(string|array $dataset): self
        {
            self::testExecutions()->push([
                'type' => 'dataset',
                'value' => $dataset,
            ]);

            return $this;
        }
    }
}

function test_alternative(string $description, Closure $callback)
{
    return new PestStoryBoardTestFunction($description, $callback);
}

test('storyboard test function will call upon the pest test function for each story in its board', function (bool $datasetEnabled) use (&$testExecutions) {
    // Swap out the test function for our test function
    Config::setAlias('test', 'test_alternative');

    // clean slate
    PestStoryBoardTestFunction::flush();

    if ($datasetEnabled) {
        Config::enableDatasets();
    } else {
        Config::disableDatasets();
    }

    Story::make()
        ->name('parent')
        ->can()
        ->action(fn () => null)
        ->assert(fn () => null)
        ->stories([
            Story::make('child a'),
            Story::make('child b'),
            Story::make()->stories([
                Story::make('child c1'),
                Story::make('child c2'),
            ]),
        ])
        ->test();

    $testExecutions = PestStoryBoardTestFunction::testExecutions();

    if ($datasetEnabled) {
        $names = [
            '[Can] child a',
            '[Can] child b',
            '[Can] child c1',
            '[Can] child c2',
        ];

        expect($testExecutions)->toHaveCount(2);
        $testExecutions = $testExecutions->pluck('value', 'type');
        expect($testExecutions)->toHaveKeys([
            'function',
            'dataset',
        ]);

        expect($testExecutions['function'])->toBeArray()->toHaveKeys(['description', 'callback'])
            ->and($testExecutions['function']['description'])->toBe('parent');

        expect($testExecutions['dataset'])->toBeArray()->toHaveKeys($names);
    } else {
        $names = [
            '[Can] parent child a',
            '[Can] parent child b',
            '[Can] parent child c1',
            '[Can] parent child c2',
        ];

        expect($testExecutions)->toHaveCount(4);
        $testExecutions = $testExecutions->keyBy(fn (array $data) => $data['value']['description']);
        expect($testExecutions)->toHaveCount(4);

        expect($testExecutions->keys()->toArray())->toBe($names);
    }

    PestStoryBoardTestFunction::flush();
    Config::setAlias('test', 'test');
})->with([
    'datasets enabled' => true,
    'datasets disabled' => false,
]);

test('using an alternative test function will throw an exception if it does not exist', function () {
    Config::setAlias('test', 'pest_storyboard_test_function_that_does_not_exist');
    Config::getAliasFunction('test');
})->throws(AliasNotFoundException::class, 'The `test` alias function `pest_storyboard_test_function_that_does_not_exist` was not found');

test('the test function is given a callback that sets the testcase and runs the story', function () {
    $ran = collect();
    $story = Story::make('test')->can(fn () => null)->action(fn () => $ran[] = 'ran');

    $reflect = new ReflectionMethod($story, 'getTestCallback');
    $reflect->setAccessible(true);

    $callback = $reflect->invoke($story);

    Closure::bind($callback, $this)($story);

    expect($story->getTest())->toBe($this);
    expect($ran->toArray())->toBe([
        'ran',
    ]);
});
