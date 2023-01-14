<?php

use BradieTilley\StoryBoard\Story;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

test('a story can record pending context changes to the cache', function () {
    $story = Story::make()
        ->setCache('peststoryboard_testkey', 'b')
        ->setCache([
            'peststoryboard_testkey2' => 'c',
        ]);

    // Preflight check
    expect(Cache::get('peststoryboard_testkey'))->toBe(null);
    expect(Cache::get('peststoryboard_testkey2'))->toBe(null);

    // Boot
    $story->bootPendingContext();

    // Assert
    expect(Cache::get('peststoryboard_testkey'))->toBe('b');
    expect(Cache::get('peststoryboard_testkey2'))->toBe('c');
});

test('a story can record pending context changes to the config', function () {
    $story = Story::make()
        ->setConfig('peststoryboard_testkey', 'b')
        ->setConfig([
            'peststoryboard_testkey2' => 'c',
        ]);

    // Preflight check
    expect(Config::get('peststoryboard_testkey'))->toBe(null);
    expect(Config::get('peststoryboard_testkey2'))->toBe(null);

    // Boot
    $story->bootPendingContext();

    // Assert
    expect(Config::get('peststoryboard_testkey'))->toBe('b');
    expect(Config::get('peststoryboard_testkey2'))->toBe('c');
});

test('a story can record pending context changes to the session', function () {
    $story = Story::make()
        ->setSession('peststoryboard_testkey', 'b')
        ->setSession([
            'peststoryboard_testkey2' => 'c',
        ]);

    // Preflight check
    expect(Session::get('peststoryboard_testkey'))->toBe(null);
    expect(Session::get('peststoryboard_testkey2'))->toBe(null);

    // Boot
    $story->bootPendingContext();

    // Assert
    expect(Session::get('peststoryboard_testkey'))->toBe('b');
    expect(Session::get('peststoryboard_testkey2'))->toBe('c');
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
