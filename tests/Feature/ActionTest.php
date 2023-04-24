<?php

use BradieTilley\Stories\Concerns\Stories;
use BradieTilley\Stories\Exceptions\StoryActionInvalidException;
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Story;
use Illuminate\Support\Collection;
use Tests\Fixtures\AnExampleAction;
use Tests\Fixtures\NonActionExample;

uses(Stories::class);

test('a class-based action with an invoke method can be referenced by name, namespace or instance', function () {
    AnExampleAction::$ran = [];
    $action = AnExampleAction::make();

    story()
        ->action(fn (Story $story) => $story->setData('abc', 8))
        ->action(AnExampleAction::class)
        ->action('an_example_action')
        ->action($action);

    expect(AnExampleAction::$ran)->toBe([
        'abc:8',
        'abc:16',
        'abc:32',
    ]);
});

test('a class-based action cannot be used if it is not a valid Action', function () {
    story()->action(NonActionExample::class);
})->throws(StoryActionInvalidException::class);

test('an action can be passed required arguments', function () {
    action('do_something', function (string $foo, int $bar, float $baz, array $qux) {
        return [
            'string' => $foo,
            'int' => $bar,
            'float' => $baz,
            'array' => $qux,
        ];
    }, 'result');

    $story = story()->action('do_something', [
        'foo' => 'Working',
        'bar' => 123,
        'baz' => 4.5,
        'qux' => [6, 7, 8, 9, 0],
    ]);
    $result = $story->getData('result');

    expect($result)->toBe([
        'string' => 'Working',
        'int' => 123,
        'float' => 4.5,
        'array' => [6, 7, 8, 9, 0],
    ]);
});

test('nested actions', function () {
    $ran = Collection::make();

    action('a1', fn () => $ran[] = 'a1');
    action('a2', fn () => $ran[] = 'a2');

    action('b1', fn () => $ran[] = 'b1');
    action('b2', fn () => $ran[] = 'b2');

    action('c1', fn () => $ran[] = 'c1');
    action('c2', fn () => $ran[] = 'c2');

    action('a', fn () => $ran[] = 'a')->action('a1')->action('a2');
    action('b', fn () => $ran[] = 'b')->action('b1')->action('b2');
    action('c', fn () => $ran[] = 'c')->action('c1')->action('c2');

    action('all', fn () => $ran[] = 'all')->action('a')->action('b')->action('c');

    story()->action('all');

    expect($ran->toArray())->toBe([
        'a1',
        'a2',
        'a',
        'b1',
        'b2',
        'b',
        'c1',
        'c2',
        'c',
        'all',
    ]);
});
