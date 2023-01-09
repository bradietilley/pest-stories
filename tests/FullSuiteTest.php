<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;
use BradieTilley\StoryBoard\StoryBoard;
use PHPUnit\Framework\TestCase;

$data = collect([
    'shared_action' => null,
    'actions' => null,
    'can' => null,
    'cannot' => null,
]);

/**
 * Callback to assert the data is correctly updated (indicating all actions and actions were run)
 */
function expectTestSuiteRun(&$data): void
{
    expect($data['shared_action'])->toBe('1')
        ->and($data['action'])->toBe('3')
        ->and($data['can'])->toBe('[Can] full suite test with child one')
        ->and($data['cannot'])->toBe('[Cannot] full suite test with child two')
        ->and($data['testcase'])->toBe('P\\Tests\\FullSuiteTest');

    // reset
    $data = collect([
        'shared_action' => null,
        'action' => null,
        'can' => null,
        'cannot' => null,
    ]);
}

/**
 * Test Action to be executed during Story boot
 */
Action::make('shared_action', function () use (&$data) {
    $data['shared_action'] = '1';

    return 1;
}, 'shared');

/**
 * Test Action to be executed during Story boot
 */
Action::make('action_one', function () use (&$data) {
    $data['action'] = '2';

    return 2;
}, 'action');

/**
 * Test Action to be executed during Story boot
 */
Action::make('action_two', function () use (&$data) {
    $data['action'] = '3';

    return 3;
}, 'action');

/**
 * Create a storyboard with a shared action and two child tests
 */
$story = StoryBoard::make()
    ->name('full suite test')
    ->action('shared_action')
    ->before(fn () => null)
    ->action(function (Story $story, TestCase $test, $shared, $action) use (&$data) {
        $actions = $data['actions'];
        $actions[] = [
            'shared' => $shared,
            'action' => $action,
            'story' => $story->getTestName(),
        ];

        $data['actions'] = $actions;
        $data['testcase'] = get_class($test);
    })
    ->assert(
        function (Story $story) use (&$data) {
            $data['can'] = $story->getTestName();

            expect(true)->toBeTrue();
        },
        function (Story $story) use (&$data) {
            $data['cannot'] = $story->getTestName();

            expect(true)->toBeTrue();
        },
    )
    ->stories([
        Story::make()
            ->name('with child one')
            ->action('action_one')
            ->can(),
        Story::make()
            ->name('with child two')
            ->action('action_two')
            ->cannot(),
    ])
    ->test();

/**
 * Assert that the above test cases were run correctly
 */
test('check the full suite test ran correctly (manual)', function () use (&$data) {
    expectTestSuiteRun($data);
});
