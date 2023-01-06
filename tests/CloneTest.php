<?php

use BradieTilley\StoryBoard\Story\Scenario;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

test('you can clone a scenario and it will have a different identifier', function () {
    $scenario1 = Scenario::make('something_common')->as(fn () => null);
    $scenario2 = $scenario1->clone();

    expect($scenario1->getName())->not()->toBe($scenario2->getName());
});

test('you can add a cloned scenario to a story', function () {
    $data = Collection::make();

    $scenario1 = Scenario::make('something_common')->as(fn () => $data[] = 'something_common')->appendName('old name');
    $scenario2 = $scenario1->clone()->as(fn () => $data[] = 'something_not_so_common')->appendName('new name');

    $story = StoryBoard::make('a story')
        ->can()
        ->check(fn () => null)
        ->task(fn () => null)
        ->scenario($scenario2)
        ->boot()
        ->assert();

    expect($story->getTestName())->toBe('[Can] a story new name');

    expect($data->toArray())->toBe([
        'something_not_so_common',
    ]);
});
