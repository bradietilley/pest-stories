<?php

use BradieTilley\Stories\Concerns\Stories;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Story;
use Tests\Fixtures\DeferrableAction;
use Tests\Fixtures\DeferredAction;

uses(Stories::class);

test('an action can be created and deferred execution', function () {
    Story::setInstance(story());
    DeferrableAction::$ran = [];

    $action = DeferrableAction::defer()->abc()->def()->ghi();
    expect(DeferrableAction::$ran)->toBe([]);

    $action = $action->resolvePendingAction();
    expect(DeferrableAction::$ran)->toBe([
        'construct',
        'abc',
        'def',
        'ghi',
    ]);

    $action->run(story());
    expect(DeferrableAction::$ran)->toBe([
        'construct',
        'abc',
        'def',
        'ghi',
        'invoke',
    ]);
});

test('an action is not deferred invocation when created via make method - in a test story call')
    ->action(fn () => DeferrableAction::$ran = [])
    ->action(fn () => DeferrableAction::$ran[] = 'other actions')
    ->action(DeferrableAction::make()->abc()->def()->ghi())
    ->action(function () {
        expect(DeferrableAction::$ran)->toBe([
            // 'construct', // no construct as it is constructed before `$ran = []` is run
            // No abc, def, ghi as they're run before `$ran = []` is run
            'other actions',
            'invoke',
        ]);
    });

test('an action is deferred invocation when created via defer method - in a test story call')
    ->action(fn () => DeferrableAction::$ran = [])
    ->action(fn () => DeferrableAction::$ran[] = 'other actions')
    ->action(DeferrableAction::defer()->abc()->def()->ghi())
    ->action(function () {
        expect(DeferrableAction::$ran)->toBe([
            'other actions',
            'construct', // construct is deferred
            'abc',
            'def',
            'ghi',
            'invoke',
        ]);
    });

test('a Deferred action is deferred invocation when created via make method - in a test story call')
    ->action(fn () => DeferredAction::$ran = [])
    ->action(fn () => DeferredAction::$ran[] = 'other actions')
    ->action(DeferredAction::make()->abc()->def()->ghi())
    ->action(function () {
        expect(DeferredAction::$ran)->toBe([
            'other actions',
            'construct', // construct is deferred
            'abc',
            'def',
            'ghi',
            'invoke',
        ]);
    });
