<?php

use BradieTilley\Stories\Concerns\Stories;
use BradieTilley\Stories\Exceptions\StoryActionNotFoundException;
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\story;
use BradieTilley\Stories\Story;
use Illuminate\Support\Collection;
use Pest\Factories\TestCaseMethodFactory;
use Pest\PendingCalls\TestCall;
use PHPUnit\Framework\IncompleteTestError;

uses(Stories::class);

action('does_nothing', fn () => null);
action('asserts_something', fn () => expect(true)->toBeTrue());
action('create_product', function () {
    return [
        'id' => 123,
        'title' => 'Test',
        'sku' => 'test',
    ];
})->variable('product');

test('it will fail if no actions do anything')
    ->action('does_nothing')
    ->throws(IncompleteTestError::class, '');

test('can do something like in pest')
    ->action('asserts_something');

test('can do something nice with variables')
    ->action(function (Story $story) {
        expect($story->getData('product'))->toBeNull();
    })
    ->action('create_product')
    ->action(function (Story $story, array $product) {
        $expect = [
            'id' => 123,
            'title' => 'Test',
            'sku' => 'test',
        ];

        expect($story->getData('product'))->toBe($expect);
        expect($product)->toBe($expect);
    });

test('another test will not inherit story variables from another story')
    ->action(function (Story $story) {
        expect($story->getData('product'))->toBeNull();
    });

test('an action with a name uses the given name')
    ->action(function () {
        $action = action('test name');

        expect($action->getName())->toBe('test name');
    });

test('an action with no name is given a random name', function () {
    $action = action();

    expect($action->getName())
        ->toMatch('/^BradieTilley\\\Stories\\\Action@[a-zA-Z0-9]+$/');
});

test('an action may be defined a custom name after the fact', function () {
    $action = action();
    expect($action->getName())->not->toBe('something nice');

    $action->name('something nice');
    expect($action->getName())->toBe('something nice');
});

test('an action can be given a callback by using the as method', function () {
    $ran = Collection::make();

    $action = action('test_action')->as(fn () => $ran[] = 'yes');
    $action->run(story());

    expect($ran->toArray())->toBe([
        'yes',
    ]);
});

test('an action can require other actions recursively', function () {
    $ran = Collection::make();

    action('a')->as(fn () => $ran[] = 'a');
    action('b')->as(fn () => $ran[] = 'b')->action('a');
    action('c')->as(fn () => $ran[] = 'c');
    $action = action('d')->as(fn () => $ran[] = 'd')->action('b')->action('c');
    $action->run(story());

    expect($ran->toArray())->toBe([
        'a',
        'b',
        'c',
        'd',
    ]);
});

test('an action will use the action name for the variable name by default', function () {
    $action = action('something_cool');
    expect($action->getVariable())->toBe('something_cool');
});

test('an action can have a custom variable name specified', function () {
    $action = action('something_cool')->variable('even_better');
    expect($action->getVariable())->toBe('even_better');
});

$test = test('a test case which will not be run because it is not destructed');

test('you can continue to run TestCall methods after queueing an action', function () use ($test) {
    $ran = Collection::make();

    expect($test)->toBeInstanceOf(TestCall::class);
    $test = $test->action(fn () => $ran[] = 'action');
    expect($test)->toBeInstanceOf(TestCall::class);

    $method = new ReflectionProperty($test, 'testCaseMethod');
    $method->setAccessible(true);
    /** @var TestCaseMethodFactory $testCaseMethod */
    $testCaseMethod = $method->getValue($test);

    expect($testCaseMethod->proxies->count('expectException'))->toBe(0);
    expect($testCaseMethod->groups)->toBe([]);

    // A method available only on TestCall
    $test->throws(InvalidArgumentException::class)->group('testgroup');

    expect($testCaseMethod->proxies->count('expectException'))->toBe(1);
    expect($testCaseMethod->groups)->toBe([
        'testgroup',
    ]);
});

test('a story will fail when fetching an action that does not exist')
    ->action('this action does not exist')
    ->throws(StoryActionNotFoundException::class, 'Story action `this action does not exist` could not be found');

test('a story can call a null callback safely', function () {
    expect(story()->callCallback(null))->toBeNull();
});

test('a story data repository supports checking existence', function () {
    $story = story();

    expect($story->hasData('foo'))->toBeFalse();

    $story->setData('foo', [
        'bar' => [
            'baz' => 123,
        ],
    ]);

    expect($story->hasData('foo'))->toBeTrue();
    expect($story->hasData('foo.bar'))->toBeTrue();
    expect($story->hasData('foo.bar.baz'))->toBeTrue();
});
