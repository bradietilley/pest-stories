# Pest Stories

A clean approach for writing large test suites.

![Static Analysis](https://github.com/bradietilley/pest-stories/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/pest-stories/actions/workflows/tests.yml/badge.svg)

## Installation

```
composer require bradietilley/pest-stories --dev
```

## Introduction

User stories are short, simple descriptions of a feature or functionality of a software application, typically written from the perspective of an end user.

Pest Stories is a PHP package that extends the PestPHP testing framework, allowing developers to write user stories in a clear and reusable way, making it easier to maintain and test their software applications.

## Documentation

Pest Stories can really boil down to a couple key areas:

- Stories
- Actions


### Stories

When you create a Pest Story a pending `Story` object is created. Throughout the lifecycle of the story - from the test's set up to the test's tear down - this story is available, namely to every action that gets invoked against it. The story acts as a hub for holding data returned from each action and sharing these key pieces of information to each subsequent action thereafter.

**Create a story test**

You can create a Story test using the following syntax:

```php

test('your test case name')
    ->action()->...;

// or

test('you test case name 2')
    ->story()->...;
```

**Interacting with data repository:**

```php
test('your test')
    ->action(function (Story $story) {
        // Get a variable
        $bar = $story->getData('foor.bar', default: null);

        // Set a variable
        $story->setData('foo.baz', value: 123);

        // Check existence of a variable
        if ($story->hasData('foo.baz')) {
            // do something
        }

        // Get all variables
        $data = $story->allData();
    });
```

### Actions

Actions are typically short reusable code snippets that are designed to be used in various tests and they can represent either a small fragment of your overall system or a large pre-existing scenario that a test case must seed before running anything.

Adding actions to a test is extremely simple and operates in a similar way to other Pest features like arch testing.

In this example below we'll test a few areas of a card game that's built in Laravel:

```php
/**
 * Defining actions
 */
action('create_game', function () {
    $game = Game::factory()->create();
}, 'game');

action('join_game', function (Game $game, string $username) {
    $game->players()->save(
        new Player([
            'username' => $username,
        ]),
    );
});

action('deal_game', function (Game $game) {
    $game->start()->deal();
});

action('base_game')
    ->action('create_game')
    ->action('join_game', [ 'username' => 'Jason Statham' ])
    ->action('join_game', [ 'username' => 'Dwayne Johnson' ])
    ->action('join_game', [ 'username' => 'Vin Diesel' ])
    ->action('create_game');

action('get_turn', function (Game $game) {
    return $game->refresh()->turn;
}, 'turn');

action('play_turn', function (Turn $turn, TestCase $test, ?string $cardToPlay = null) {
    actingAs($turn->player->user);

    $card = ($cardToPlay === null)
        ? $turn->player->hand->cards->random()
        : $turn->player->hand->cards->where('uuid', $cardToPlay)->first();

    return $test->post(route('turns.play', [ 'turn' => $turn ]), [
        'card' => $card?->uuid,
    ]);
}, 'response');

action('assert:ok', function (TestResponse $response) {
    $response->assertOk();
});

action('is_turn', function (Game $game, string $player) {
    $actual = $game->refresh()->turn->player->username;

    expect($actual)->toBe($player);
});

/**
 * Adding actions to tests
 */
test('when a game is dealt, one player is given a turn')
    ->action('base_game')
    ->action('get_turn')
    ->action('is_turn', 'Jason Statham');

test('when a game is dealt and the player plays their turn, the next person is given a turn')
    ->action('base_game')
    ->action('is_turn', 'Jason Statham')

    ->action('play_turn')
    ->action('is_turn', 'Dwayne Johnson')

    ->action('play_turn')
    ->action('is_turn', 'Vin Diesel')

    ->action('play_turn')
    ->action('is_turn', 'Jason Statham');
```

As the above demonstrates, you can reference the actions you want to run by their `name`. 

#### Different ways to add actions

**Using name identifiers:**

```php
action('do_something', function () {
    dump('do something here');
})

test('can do something')
    ->action('do_something');
```

**Using inline closures:**

```php
test('can do something')
    ->action(function () {
        dump('do something here');
    });
```

**Using Action class names:**

```php
use BradieTilley\Stories\Action;

class MyAction extends Action
{
    public function __invoke()
    {
        dump('do something here');
    }
}

test('can do something')
    ->action(MyAction::class);
```

**Using instantiated action classes:**

```php
use BradieTilley\Stories\Action;

class MyAction extends Action
{
    public function __invoke()
    {
        dump('do something here');
    }

    public function withSomething(): self
    {
        //

        return $this;
    }
}

test('can do something')
    ->action(MyAction::make()->withSomething());
```

#### Action variables

Actions may return a variable after being invoked. This value is shared back to the parent story's variable repository (see above for more information)  

#### Action Callbacks

When ran, action callbacks are invoked with Laravel's container dependency injection - so you can retrieve any singleton or bound interface class you wish by utilising typed arguments.

In Pest Stories, the dependency injection is given a few extra helpful differences:

- `\BradieTilley\Stories\Story $story` yields the current story.
- `\PHPUnit\Framework\TestCase $test` yields the current test case (which may be your `Tests\TestCase`).
- any variable stored in the data repository will yield its value.

For example:

```php
action('create_product', function () {
    return Product::factory()->create();
}, variable: 'product');

test('can do something')
    ->action('create_product')
    ->action(function (Story $story, TestCase $test, Product $product) {
        // $story is the underlying story for the test
        // $test is the underlying test from `test('can do something')`
        // $product is derived from the returned value from 'create_product'
        $product === $story->getData('product'); // true
    });
```

#### Nested Actions

You may wish to alias a single action to many other actions. When invoked, all other actions will be invoked in their respective order.

For example:

```php
action('a1', fn () => dump('a1'));
action('a2', fn () => dump('a2'));

action('b1', fn () => dump('b1'));
action('b2', fn () => dump('b2'));

action('c1', fn () => dump('c1'));
action('c2', fn () => dump('c2'));

action('a', fn () => dump('a'))->action('a1')->action('a2');
action('b', fn () => dump('b'))->action('b1')->action('b2');
action('c', fn () => dump('c'))->action('c1')->action('c2');

action('all', fn () => dump('all'))->action('a')->action('b')->action('c');

test('do a lot of things')
    ->action('all');

/**
 * a1
 * a2
 * a
 * b1
 * b2
 * b
 * c1
 * c2
 * c
 * all
 */
```