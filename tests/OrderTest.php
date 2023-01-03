<?php

use BradieTilley\StoryBoard\Story\Scenario;
use BradieTilley\StoryBoard\Story\Task;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

test('order of everything is as expected', function () {
    $data = Collection::make();

    Task::make('task')
        ->as(fn () => $data[] = "task run")
        ->registering(fn () => $data[] = "task register")
        ->booting(fn () => $data[] = "task boot");

    Scenario::make('scenario')
        ->as(fn () => $data[] = "scenario run")
        ->registering(fn () => $data[] = "scenario register")
        ->booting(fn () => $data[] = "scenario boot");

    $story = StoryBoard::make()
        ->can()
        ->before(fn () => $data[] = "task before")
        ->task('task')
        ->scenario('scenario')
        ->after(fn () => $data[] = "task after")
        ->check(fn () => $data[] = "assert run");

    $story->boot()->assert();

    expect($data->toArray())->toBe([
        'scenario register',
        'task register',
        'scenario boot',
        'scenario run',
        'task before',
        'task boot',
        'task run',
        'task after',
        'assert run',
    ]);
});