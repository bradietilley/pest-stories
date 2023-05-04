<?php

use BradieTilley\Stories\Action;
use BradieTilley\Stories\Concerns\Stories;
use BradieTilley\Stories\Exceptions\StoryActionInvalidException;
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Story;
use Illuminate\Support\Collection;
use Tests\Fixtures\AnExampleAction;
use Tests\Fixtures\AnExampleActionWithSomething;
use Tests\Fixtures\DeferredAction;
use Tests\Fixtures\NonActionExample;

uses(Stories::class);

test('can determine if a class is an action or not', function () {
    expect(Action::isAction('Exception'))->toBeFalse();
    expect(Action::isAction(NonActionExample::class))->toBeFalse();
    expect(Action::isAction('NotExists'))->toBeFalse();

    expect(Action::isAction(Action::class))->toBeTrue();
    expect(Action::isAction(AnExampleAction::class))->toBeTrue();
    expect(Action::isAction(DeferredAction::class))->toBeTrue();
});

test('a class-based action with an invoke method can be referenced by name, namespace or instance', function () {
    AnExampleAction::$ran = [];
    $action = AnExampleAction::make();

    story()
        ->action(fn (Story $story) => $story->set('abc', 8))
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

test('a class-based action is unique when used multiple times', function () {
    story()
        ->action($abc = AnExampleActionWithSomething::make()->withSomething('abc'))
        ->action($def = AnExampleActionWithSomething::make()->withSomething('def'))
        ->action($ghi = AnExampleActionWithSomething::make()->withSomething('ghi'));

    expect(AnExampleActionWithSomething::$ran)->toBe([
        'something:abc',
        'something:def',
        'something:ghi',
    ]);

    /**
     * Expect each name to be different as the class offers
     * no name so will be given a random one each time.
     */
    expect($abc->getName())
        ->not->toBe($def->getName())
        ->not->toBe($ghi->getName());
    expect($def->getName())
        ->not->toBe($ghi->getName());
});

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
    $result = $story->get('result');

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

test('the result of an action is returned from the run and process methods')
    ->action(function () {
        $result = AnExampleAction::make()->run(arguments: [
            'abc' => 8,
        ]);

        expect($result)->toBe(16);
    });

test('an action that returns another action results in the inner action being invoked')
    ->action(fn () => null)
    ->action(fn () => AnExampleAction::make(), [
        'abc' => 123,
    ])
    ->action(function (int $abc) {
        expect($abc)->toBe(246);
    });

test('an action that returns another action results in the inner action being invoked - deferred')
    ->action(fn () => null)
    ->action(fn () => AnExampleAction::defer(), [
        'abc' => 123,
    ])
    ->action(function (int $abc) {
        expect($abc)->toBe(246);
    });
