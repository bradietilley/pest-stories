<?php

use BradieTilley\StoryBoard\Scenario;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\StoryBoard;

$data = collect([
    'shared_scenario' => null,
    'scenario' => null,
    'tasks' => [],
    'can' => null,
    'cannot' => null,
]);

Scenario::make('shared_scenario', 'shared', function () use (&$data) {
    $data['shared_scenario'] = '1';

    return 1;
});

Scenario::make('scenario_one', 'scenario', function () use (&$data) {
    $data['scenario'] = '2';

    return 2;
});

Scenario::make('scenario_two', 'scenario', function () use (&$data) {
    $data['scenario'] = '3';

    return 3;
});

$story = StoryBoard::make()
    ->name('full suite test')
    ->scenario('shared_scenario')
    ->task(function (Story $story, $shared, $scenario) use (&$data) {
        $tasks = $data['tasks'];
        $tasks[] = [
            'shared' => $shared,
            'scenario' => $scenario,
            'story' => $story->getFullName(),
        ];

        $data['tasks'] = $tasks;
    })
    ->check(
        function (Story $story) use (&$data) {
            $data['can'] = $story->getFullName();

            expect(true)->toBeTrue();
        },
        function (Story $story) use (&$data) {
            $data['cannot'] = $story->getFullName();

            expect(true)->toBeTrue();
        },
    )
    ->stories([
        Story::make()
            ->name('with child one')
            ->scenario('scenario_one')
            ->can(),
        Story::make()
            ->name('with child two')
            ->scenario('scenario_two')
            ->cannot(),
    ]);

$story->test();

test('check the full suite test ran correctly', function () use ($data) {
    expect($data['shared_scenario'])->toBe('1')
        ->and($data['scenario'])->toBe('3')
        ->and($data['can'])->toBe('[Can] full suite test with child one')
        ->and($data['cannot'])->toBe('[Cannot] full suite test with child two')
        ->and($data['tasks'])->toBeArray()->toHaveCount(2)
        ->and($data['tasks'][0])->toBe([
            'shared' => 1,
            'scenario' => 2,
            'story' => '[Can] full suite test with child one',
        ])
        ->and($data['tasks'][1])->toBe([
            'shared' => 1,
            'scenario' => 3,
            'story' => '[Cannot] full suite test with child two',
        ]);
});