<?php

use function BradieTilley\StoryBoard\debug;
use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;

test('debug information is available after a story is run', function () {
    Action::make('do_something', fn () => 'test', 'something');

    $story = Story::make()
        ->action('do_something')
        ->can(fn () => null);

    $story->assignDebugContainer()->run();

    $debug = debug()->prepareForDumping()->values();

    expect($debug)->toContain('Story created');
    expect($debug)->toContain('Test::run() start');
    expect($debug)->toContain('Test::run() success');
});
