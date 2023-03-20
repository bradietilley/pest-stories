<?php

use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\assertion;
use BradieTilley\Stories\Helpers\CallbackRepository;
use function BradieTilley\Stories\Helpers\story;
use Illuminate\Support\Collection;

CallbackRepository::flush();
$ran = Collection::make();

action('action_1')
    ->before(fn () => $ran[] = 'action 1 before')
    ->after(fn () => $ran[] = 'action 1 after')
    ->as(fn () => $ran[] = 'action 1 primary');
action('action_2')
    ->before(fn () => $ran[] = 'action 2 before')
    ->after(fn () => $ran[] = 'action 2 after')
    ->as(fn () => $ran[] = 'action 2 primary');

assertion('assertion_1')
    ->before(fn () => $ran[] = 'assertion 1 before')
    ->after(fn () => $ran[] = 'assertion 1 after')
    ->as(fn () => $ran[] = 'assertion 1 primary');
assertion('assertion_2')
    ->before(fn () => $ran[] = 'assertion 2 before')
    ->after(fn () => $ran[] = 'assertion 2 after')
    ->as(fn () => $ran[] = 'assertion 2 primary');

story('real life example story registers correctly')
    ->setUp(fn () => $ran[] = 'setUp')
    ->tearDown(fn () => $ran[] = 'tearDown')
    ->before(fn () => $ran[] = 'before')
    ->after(fn () => $ran[] = 'after')
    ->as(fn () => $ran[] = 'primary')
    ->action('action_1')
    ->action('action_2')
    ->assertion('assertion_1')
    ->assertion('assertion_2')
    ->assertion(fn () => expect(true)->toBeTrue())
    ->register();

test('real life example story ran correctly', function () use ($ran) {
    expect($ran->toArray())->toBe([
        'setUp',
        'action 1 before',
        'action 1 primary',
        'action 1 after',
        'action 2 before',
        'action 2 primary',
        'action 2 after',
        'before',
        'primary',
        'after',
        'assertion 1 before',
        'assertion 1 primary',
        'assertion 1 after',
        'assertion 2 before',
        'assertion 2 primary',
        'assertion 2 after',
        'tearDown',
    ]);
});

$ran2 = Collection::make();

story('real life example nested story')
    ->action(fn () => $ran2[] = 'parent story')
    ->stories([
        story('a child story 1')->action(fn () => $ran2[] = 'child story 1'),
        story('a child story 2')->action(fn () => $ran2[] = 'child story 2'),
    ])
    ->assertion(fn () => expect(true)->toBeTrue())
    ->register();

test('real life example nested story ran correctly', function () use ($ran2) {
    expect($ran2->toArray())->toBe([
        'parent story',
        'child story 1',
        'parent story',
        'child story 2',
    ]);
});
