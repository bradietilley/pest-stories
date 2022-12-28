<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Scenario;
use BradieTilley\StoryBoard\StoryBoard;

$data = collect([
    'shared_scenario' => null,
    'scenario' => null,
    'tasks' => [],
    'can' => null,
    'cannot' => null,
]);

/**
 * Callback to assert the data is correctly updated (indicating all scenarios and tasks were run)
 */
function expectTestSuiteRun(&$data): void
{
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

    // reset
    $data = collect([
        'shared_scenario' => null,
        'scenario' => null,
        'tasks' => [],
        'can' => null,
        'cannot' => null,
    ]);
}

/**
 * Test Scenario to be executed during Story boot
 */
Scenario::make('shared_scenario', function () use (&$data) {
    $data['shared_scenario'] = '1';

    return 1;
}, 'shared');

/**
 * Test Scenario to be executed during Story boot
 */
Scenario::make('scenario_one', function () use (&$data) {
    $data['scenario'] = '2';

    return 2;
}, 'scenario');

/**
 * Test Scenario to be executed during Story boot
 */
Scenario::make('scenario_two', function () use (&$data) {
    $data['scenario'] = '3';

    return 3;
}, 'scenario');

/**
 * Create a storyboard with a shared scenario and two child tests
 */
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

/**
 * Manually register the test cases
 */
test($story->getFullName().' (manual)', function (Story $story) {
    $story->boot()->assert();
})->with($story->allStories());

/**
 * Assert that the above test cases were run correctly
 */
test('check the full suite test ran correctly (manual)', function () use (&$data) {
    expectTestSuiteRun($data);
});
