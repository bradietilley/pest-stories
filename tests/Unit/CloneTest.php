<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;
use Illuminate\Support\Collection;

test('you can clone a action and it will have a different identifier', function () {
    $action1 = Action::make('something_common')->as(fn () => null);
    $action2 = $action1->clone();

    expect($action1->getName())->not()->toBe($action2->getName());
});

test('you can add a cloned action to a story', function () {
    $data = Collection::make();

    $action1 = Action::make('something_common')->as(fn () => $data[] = 'something_common')->appendName('old name');
    $action2 = $action1->clone()->as(fn () => $data[] = 'something_not_so_common')->appendName('new name');

    $story = Story::make('a story')
        ->can()
        ->assert(fn () => null)
        ->action($action2)
        ->boot()
        ->perform();

    expect($story->getTestName())->toBe('[Can] a story new name');

    expect($data->toArray())->toBe([
        'something_not_so_common',
    ]);
});
