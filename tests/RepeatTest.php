<?php

use BradieTilley\StoryBoard\Story\Scenario;
use BradieTilley\StoryBoard\StoryBoard;
use Illuminate\Support\Collection;

test('a scenario can be run once', function () {
    $run = Collection::make();
   
    Scenario::make('something')->as(fn () => $run[] = 'scenario');
    
    StoryBoard::make('story')
        ->can()
        ->check(fn () => null)
        ->task(fn () => $run[] = 'task')
        ->scenario('something')
        ->boot()
        ->assert();
    
    expect($run->toArray())->toBe([
        'scenario',
        'task',
    ]);
});

test('a scenario can be run multiple times', function () {
    $run = Collection::make();
   
    Scenario::make('something')->as(fn () => $run[] = 'scenario')->repeat(3);
    
    StoryBoard::make('story')
        ->can()
        ->check(fn () => null)
        ->task(fn () => $run[] = 'task')
        ->scenario('something')
        ->boot()
        ->assert();
    
    expect($run->toArray())->toBe([
        'scenario',
        'scenario',
        'scenario',
        'task',
    ]);
});

test('a scenario can be run zero times', function () {
    $run = Collection::make();
   
    Scenario::make('something')->as(fn () => $run[] = 'scenario')->repeat(0);
    
    StoryBoard::make('story')
        ->can()
        ->check(fn () => null)
        ->task(fn () => $run[] = 'task')
        ->scenario('something')
        ->boot()
        ->assert();
    
    expect($run->toArray())->toBe([
        'task',
    ]);
});