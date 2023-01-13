<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Action;

test('the story data can shared easily amongst callbacks', function () {
    Action::make('something_cool')->as(function (Story $story) {
        return [
            'a',
        ];
    })->variable('list');

    $story = Story::make()
        ->name('something')
        ->can()
        ->action('something_cool')
        ->before(function (Story $story) {
            // nothing
        })
        ->action(function (Story $story) {
            expect($story->has('list'))->toBeTrue();

            $list = $story->get('list', []);
            $list[] = 'b';
            $story->set('list', $list);
        })
        ->after(function (Story $story) {
            expect($story->has('list'))->toBeTrue();

            $list = $story->get('list', []);
            $list[] = 'c';
            $story->set('list', $list);
        })
        ->assert(function (Story $story) {
            expect($story->has('list'))->toBeTrue();

            $list = $story->get('list', []);
            $list[] = 'd';
            $story->set('list', $list);
        });

    $story->boot()->perform();

    expect($story->all())->toBeArray()->toHaveKey('list');

    expect($story->get('list'))->toBe([
        'a',
        'b',
        'c',
        'd',
    ]);
});

test('a story may inherit variables from its parent stories', function () {
    $story = Story::make('parent')
        ->set('foo', '1')
        ->set('bar', 2)
        ->set('old', 'yes')
        ->stories([
            Story::make('child 1')->set('foo', '2'),
            Story::make('child 2')->set('bar', false)->set('new', true),
        ]);

    $data = $story->storiesAll
        ->map(fn (Story $story) => [
            'data' => $story->all(),
            'name' => $story->getName(),
        ])
        ->pluck('data', 'name')
        ->toArray();

    expect($data)->toBe([
        'child 1' => [
            'foo' => '2',
            'bar' => 2,
            'old' => 'yes',
        ],
        'child 2' => [
            'foo' => '1',
            'bar' => false,
            'old' => 'yes',
            'new' => true,
        ],
    ]);
});

test('you can bulk set data by passing an array into set method', function () {
    $story = Story::make()->set('a', 1)->set('b', 2);

    expect($story->all())->toBe([
        'a' => 1,
        'b' => 2,
    ]);

    $story->set([
        'b' => 3,
        'c' => 4,
    ]);

    expect($story->all())->toBe([
        'a' => 1,
        'b' => 3,
        'c' => 4,
    ]);
});
