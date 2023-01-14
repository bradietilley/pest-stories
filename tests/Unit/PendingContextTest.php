<?php

use BradieTilley\StoryBoard\Story;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

test('a story can record pending context changes to the cache', function () {
    $story = Story::make()->setCache('peststoryboard_testkey', 'b');

    expect(Cache::get('peststoryboard_testkey'))->toBe(null);
    $story->bootPendingContext();
    expect(Cache::get('peststoryboard_testkey'))->toBe('b');
});

test('a story can record pending context changes to the config', function () {
    $story = Story::make()->setConfig('peststoryboard_testkey', 'b');

    expect(Config::get('peststoryboard_testkey'))->toBe(null);
    $story->bootPendingContext();
    expect(Config::get('peststoryboard_testkey'))->toBe('b');
});

test('a story can record pending context changes to the session', function () {
    $story = Story::make()->setSession('peststoryboard_testkey', 'b');

    expect(Session::get('peststoryboard_testkey'))->toBe(null);
    $story->bootPendingContext();
    expect(Session::get('peststoryboard_testkey'))->toBe('b');
});

test('a story can inherit pending context changes', function () {
    $story = Story::can(fn () => null)
        ->action(fn () => null)
        ->setCache('peststoryboard_testkey', '1')
        ->setConfig('peststoryboard_testkey', '2')
        ->setSession('peststoryboard_testkey', '3')
        ->stories([
            $story1 = Story::make('child 1'),
            $story2 = Story::make('child 2')->setConfig('peststoryboard_testkey', '2b'),
        ]);

    $story->storiesAll;

    $story1->run();
    expect(Cache::get('peststoryboard_testkey'))->toBe('1');
    expect(Config::get('peststoryboard_testkey'))->toBe('2');
    expect(Session::get('peststoryboard_testkey'))->toBe('3');

    $story2->run();
    expect(Cache::get('peststoryboard_testkey'))->toBe('1');
    expect(Config::get('peststoryboard_testkey'))->toBe('2b');
    expect(Session::get('peststoryboard_testkey'))->toBe('3');
});
