<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;
use BradieTilley\StoryBoard\Story\DebugContainer;

test('debug information is available after a story is run', function () {
    Action::make('do_something', fn () => 'test', 'something');

    $story = Story::make()
        ->action('do_something')
        ->can(fn () => null);
    
    $story->run();

    $debug = DebugContainer::instance()->prepareForDumping()->values();

    expect($debug)->toContain('Test::run() start');
    expect($debug)->toContain('Test::run() success');
});
