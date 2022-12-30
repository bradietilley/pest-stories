<?php

use BradieTilley\StoryBoard\Story;
use BradieTilley\StoryBoard\Story\Scenario;

test('the story data can shared easily amongst callbacks', function () {
    Scenario::make('something_cool')->as(function (Story $story) {
        return [
            'a',
        ];
    })->variable('list');

    $story = Story::make()
        ->name('something')
        ->can()
        ->scenario('something_cool')
        ->before(function (Story $story) {
            expect($story->has('list'))->toBeTrue();

            $list = $story->get('list', []);
            $list[] = 'b';
            $story->set('list', $list);
        })
        ->task(function (Story $story) {
            expect($story->has('list'))->toBeTrue();

            $list = $story->get('list', []);
            $list[] = 'c';
            $story->set('list', $list);
        })
        ->after(function (Story $story) {
            expect($story->has('list'))->toBeTrue();

            $list = $story->get('list', []);
            $list[] = 'd';
            $story->set('list', $list);
        })
        ->check(function (Story $story) {
            expect($story->has('list'))->toBeTrue();

            $list = $story->get('list', []);
            $list[] = 'e';
            $story->set('list', $list);
        });

    $story->boot()->assert();

    expect($story->all())->toBeArray()->toHaveKey('list');

    expect($story->get('list'))->toBe([
        'a',
        'b',
        'c',
        'd',
        'e',
    ]);
});
