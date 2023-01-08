<?php

use BradieTilley\StoryBoard\Exceptions\TestFunctionNotFoundException;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

$testExecutions = Collection::make([]);

if (! class_exists('PestStoryBoardTestFunction')) {
    class PestStoryBoardTestFunction
    {
        public function __construct(string $description, Closure $callback)
        {
            global $testExecutions;

            $testExecutions[] = [
                'type' => 'function',
                'value' => [
                    'description' => $description,
                    'callback' => $callback,
                ],
            ];
        }

        public function with(string|array $dataset): self
        {
            global $testExecutions;

            $testExecutions[] = [
                'type' => 'dataset',
                'value' => $dataset,
            ];

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
    Story::setTestFunction('test_alternative');
    // clean slate
    $testExecutions->forget($testExecutions->keys()->toArray());

    if ($datasetEnabled) {
        StoryBoard::enableDatasets();
    } else {
        StoryBoard::disableDatasets();
    }

    StoryBoard::make()
        ->name('parent')
        ->can()
        ->task(fn () => null)
        ->check(fn () => null)
        ->stories([
            Story::make('child a'),
            Story::make('child b'),
            Story::make()->stories([
                Story::make('child c1'),
                Story::make('child c2'),
            ]),
        ])
        ->test();

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

    foreach ($testExecutions as $key => $value) {
        $testExecutions->forget($key);
    }

    Story::setTestFunction();
})->with([
    'datasets enabled' => true,
    'datasets disabled' => false,
]);

test('using an alternative test function will throw an exception if it does not exist', function () {
    Story::setTestFunction('pest_storyboard_test_function_that_does_not_exist');
})->throws(TestFunctionNotFoundException::class, 'The story test function `pest_storyboard_test_function_that_does_not_exist` could not be found');
