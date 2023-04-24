<?php

use BradieTilley\Stories\Concerns\Stories;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Story;
use Tests\Fixtures\DeferrableAction;

uses(Stories::class);

test('an action can be created and deferred execution', function () {
    Story::setInstance(story());

    $action = DeferrableAction::defer()->abc()->def()->ghi();
    expect(DeferrableAction::$ran)->toBe([]);

    $action = $action->invokePendingCall();
    expect(DeferrableAction::$ran)->toBe([
        'abc',
        'def',
        'ghi',
    ]);

    $action->run(story());
    expect(DeferrableAction::$ran)->toBe([
        'abc',
        'def',
        'ghi',
        'invoke',
    ]);
});

test('an action can be created and deferred execution when run on a test')
    ->action(fn () => DeferrableAction::$ran = [])
    ->action(DeferrableAction::defer()->abc()->def()->ghi())
    ->action(function () {
        expect(DeferrableAction::$ran)->toBe([
            'abc',
            'def',
            'ghi',
            'invoke',
        ]);
    });
