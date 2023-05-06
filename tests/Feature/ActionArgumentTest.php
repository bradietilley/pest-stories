<?php

use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\story;
use Illuminate\Support\Collection;

test('an action can be supplied arguments from the story data repository', function () {
    $story = story()->fresh()->use();
    $ran = Collection::make();

    $story->set('aac', 111);
    $story->set('abc', 123);
    $story->set('acc', 333);

    $story->action(fn (int $abc) => $ran[] = $abc);
    expect($ran->toArray())->toBeArray()->toBe([
        123,
    ]);

    $story->action(fn (int $acc) => $ran[] = $acc);
    expect($ran->toArray())->toBeArray()->toBe([
        123,
        333,
    ]);
});

test('an action can be supplied arguments from the actions own data repository', function () {
    $story = story()->fresh()->use();
    $ran = Collection::make();

    $data = [
        'aac' => 111,
        'abc' => 123,
        'acc' => 333,
    ];

    action('supply_from_own_repository_abc', fn (int $abc) => $ran[] = $abc)->merge($data);
    action('supply_from_own_repository_acc', fn (int $acc) => $ran[] = $acc)->merge($data);

    $story->action('supply_from_own_repository_abc');
    expect($ran->toArray())->toBeArray()->toBe([
        123,
    ]);

    $story->action('supply_from_own_repository_acc');
    expect($ran->toArray())->toBeArray()->toBe([
        123,
        333,
    ]);
});

test('an action can be supplied arguments from both the story and action (action replaces story args)', function () {
    $story = story()->fresh()->use();
    $ran = Collection::make();

    $story->merge([
        'aac' => 111,
        'abc' => 123,
        'acc' => 333,
    ]);

    $data = [
        'aac' => 222,
        'abc' => 246,
    ];
    action('supply_from_own_repository_abc', fn (int $abc) => $ran[] = $abc)->merge($data);
    action('supply_from_own_repository_acc', fn (int $acc) => $ran[] = $acc)->merge($data);

    $story->action('supply_from_own_repository_abc');
    expect($ran->toArray())->toBeArray()->toBe([
        246, // from action
    ]);

    $story->action('supply_from_own_repository_acc');
    expect($ran->toArray())->toBeArray()->toBe([
        246, // from action
        333, // from story
    ]);
});
