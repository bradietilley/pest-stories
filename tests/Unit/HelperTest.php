<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;

use function BradieTilley\StoryBoard\action;
use function BradieTilley\StoryBoard\story;

test('a story can be created via the story() function with actions created via the action() function', function () {
    $ran = collect();
    
    $action = action('as_admin')->as(fn () => $ran[] = 'as_admin');
    $story = story('do something')->action('as_admin')->can(fn () => null)->run();

    expect($story)->toBeInstanceOf(Story::class);
    expect($action)->toBeInstanceOf(Action::class);
    expect($ran->toArray())->toBe([
        'as_admin'
    ]);
});