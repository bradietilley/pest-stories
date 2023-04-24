<?php

use BradieTilley\Stories\Action;
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Story;
use Illuminate\Support\Collection;

test('an action will be bound to the action itself by default', function () {
    $ran = Collection::make();
    $story = story();

    $action = action('do_something');
    $action->as(function () use ($ran) {
        $ran[] = $this;
    });

    $action->run($story);

    $actual = $ran->first();
    $expect = $action;

    expect($actual === $expect)->toBeTrue();
});

test('an action will be bound to the story when it is preferred to', function () {
    $ran = Collection::make();
    $story = story();
    Story::setInstance($story);
    Action::preferBindToStory();

    $action = action('do_something');
    $action->as(function () use ($ran) {
        $ran[] = $this;
    });

    $action->run($story);

    $actual = $ran->first();
    $expect = $story;

    expect($actual === $expect)->toBeTrue();
});

test('an action will be bound to the test when it is preferred to', function () {
    $ran = Collection::make();
    $story = story();
    Story::setInstance($story);
    Action::preferBindToTest();

    $action = action('do_something');
    $action->as(function () use ($ran) {
        $ran[] = $this;
    });

    $action->run($story);

    $actual = $ran->first();
    $expect = $this;

    expect($actual === $expect)->toBeTrue();
});

test('an action will be bound to a custom object when it is preferred to', function () {
    $newThis = new class
    {
    };
    $ran = Collection::make();
    $story = story();
    Story::setInstance($story);
    Action::preferBindToObject($newThis);

    $action = action('do_something');
    $action->as(function () use ($ran) {
        $ran[] = $this;
    });

    $action->run($story);

    $actual = $ran->first();
    $expect = $newThis;

    expect($actual === $expect)->toBeTrue();
});
