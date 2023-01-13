<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;
use Illuminate\Support\Collection;

test('order of everything is as expected', function () {
    $data = Collection::make();

    Action::make('action')
        ->as(fn () => $data[] = 'action run')
        ->registering(fn () => $data[] = 'action register')
        ->booting(fn () => $data[] = 'action boot');

    $story = Story::make()
        ->can()
        ->before(fn () => $data[] = 'action before')
        ->action('action')
        ->after(fn () => $data[] = 'action after')
        ->assert(fn () => $data[] = 'assert run');

    $story->boot()->perform();

    expect($data->toArray())->toBe([
        'action register',
        'action before',
        'action boot',
        'action run',
        'action after',
        'assert run',
    ]);
});
