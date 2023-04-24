# Pest Stories

A clean approach for writing large test suites.

![Static Analysis](https://github.com/bradietilley/pest-stories/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/pest-stories/actions/workflows/tests.yml/badge.svg)


## Introduction

User stories are short, simple descriptions of a feature or functionality of a software application, typically written from the perspective of an end user.

Pest Stories is a PHP package that extends the PestPHP testing framework, allowing developers to write user stories in a clear and reusable way, making it easier to maintain and test their software applications.


## Installation

```
composer require bradietilley/pest-stories --dev
```

To add Stories to your test suites, add the following trait via Pest's `uses()` helper:

```php
uses(BradieTilley\Stories\Concerns\Stories::class);
```

*Refer to Pest's documentation on how to use the `uses()` helper.*


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
 * Defining actions - this could be done in tests/Pest.php or another test/Actions.php 
 */

/**
 * In this action we'll create a Game model and store is locally as 'game'
 */
action('create_game', function () {
    $game = Game::factory()->create();
}, 'game');

/**
 * Create a new user and player with the given username, then join the Game that is
 * stored in the local 'game' variable
 */
action('join_game', function (Game $game, string $username) {
    $game->players()->save(
        Player::factory()->create([
            'username' => $username,
        ]),
    );
});

/**
 * Simulate the Game (local 'game' variable) being dealt
 */
action('deal_game', function (Game $game) {
    $game->start()->deal();
});

/**
 * Create a game, join 3 players, then deal it.
 * 
 * This will serve as a starting point for many tests and all we have
 * to call is `->action('base_game')` 
 */
action('base_game')
    ->action('create_game')
    ->action('join_game', [ 'username' => 'Jason Statham' ])
    ->action('join_game', [ 'username' => 'Dwayne Johnson' ])
    ->action('join_game', [ 'username' => 'Vin Diesel' ])
    ->action('deal_game');

/**
 * Set the local test variable 'turn' to be the current turn determined by
 * the 'turn' relation on the Game.
 */
action('get_turn', function (Game $game) {
    return $game->refresh()->turn;
}, 'turn');

/**
 * Using the turn variable, act as the player and play either a random card
 * or a specific card in their hand. Store the API response as the 'response'
 * variable against the story
 */
action('play_turn', function (Turn $turn, TestCase $test, ?string $cardToPlay = null) {
    actingAs($turn->player->user);

    $card = ($cardToPlay === null)
        ? $turn->player->hand->cards->random()
        : $turn->player->hand->cards->where('uuid', $cardToPlay)->first();

    return $test->post(route('turns.play', [ 'turn' => $turn ]), [
        'card' => $card?->uuid,
    ]);
}, 'response');

/**
 * Assert the previous TestResponse 'response' variable was ok.
 */
action('assert:ok', function (TestResponse $response) {
    $response->assertOk();
});

/**
 * Assert that it is the given player's turn
 */
action('is_turn', function (Game $game, string $player) {
    $actual = $game->refresh()->turn->player->username;

    expect($actual)->toBe($player);
});

/**
 * Adding actions to tests. These may be in Feature or wherever else you wish. Just be sure to have added the Stories trait via uses()
 */

/**
 * 1 - base_game: create game, join 3 players, deal
 * 2 - is_turn: Assert it's Jason Statham's turn (he was first to join)
 */
test('when a game is dealt, one player is given a turn')
    ->action('base_game')
    ->action('is_turn', 'Jason Statham');

/**
 * 1 - base_game: create game, join 3 players, deal
 * 2 - is_turn: Assert it's Jason Statham's turn (he was first to join)
 * 3 - play_turn: act as turn player (Jason Statham), pick a random card and play it
 * 4 - is_turn: Assert it's Dwayne Johnson's turn (he was second to join)
 * 5 - play_turn: act as turn player (Dwayne Johsnon), pick a random card and play it
 * 6 - is_turn: Assert it's Vin Diesel's turn (he was third to join)
 * 7 - play_turn: act as turn player (Vin Diesel), pick a random card and play it
 * 8 - is_turn: Assert it's Jason Statham's turn again (come full circle)
 */
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

As the above demonstrates, you can create actions in a similar way to how you'd normally create a pest `test` using the `test($name, $callback)` syntax, then, you can add these actions to a test by referencing the name given to each action.

#### Different ways to add actions

**Using name identifiers:**

As above, a basic way to creating resuable actions using the `BradieTilley\Stories\Helpers\action` function.

```php
use BradieTilley\Stories\Helpers\action;

action('do_something', function () {
    dump('do something here');
})

test('can do something')
    ->action('do_something');

/**
 * Dumps "do something here" when the "can do something" test is run
 */
```

**Using inline closures:**

Sometimes your action may be so unique that it may not be worth creating a reusable action, so in these
cases you can instead define them as inline closures. 

```php
test('can do something')
    ->action(function () {
        dump('do something here');
    });

/**
 * Dumps "do something here" when the "can do something" test is run
 */
```

**Using Action class names:**

While the aforementioned approaches support a lot of scenarios, there may be scenarios where you need your action to store internal properties or run
a number of methods, or you may wish to add traits to your action classes to support reusable code through actions. This can be achieved by creating
an Action class that extends `BradieTilley\Stories\Action`.

Once created, you can add the action to a story/test by passing in the underlying `$name` property, or by passing in the namespace of the Action. See
below and the "Using instantiated action classes" section below as a couple examples:

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
    
/**
 * Dumps "do something here" when the "can do something" test is run
 */
```

**Using instantiated action classes:**

Carrying on from the above, you may wish to instantiate the `Action` class so that you can give the action some context or to control aspects of the action.

The `::make()` method accepts any number of arguments that are passed directly to the `__construct`, which you may override if need be. For custom action classes
you do not need to call `parent::__construct()` from your construct.

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
        // add something here

        return $this;
    }
}

test('can do something')
    ->action(MyAction::make()->withSomething());
    
/**
 * Dumps "do something here" when the "can do something" test is run
 */
```


#### Action names

It's recommended to name each action in either camel or snake case for variable purposes (see "Action Callbacks" sections below for more info).

An action created using the `action` helper function will accept a name as the first parameter. This allows the action to be added to stories/tests using the
same name, as described in the various examples above.

**Default names**

By default if you provide no name a random name will be given, meaning you'll be unable to reference it by name (unless you inspect the Actions repository).

Any class-based Action that does not have an explicit `$name` property specified will be given a random name that's prefixed with the namespace of the class, for example `Tests\Actions\CanViewModel@1n4rf90d`

**Overriding names**

The name can be overridden after-the-fact using the `->name($name)` method. Note: The `Actions` repository will have already recorded it under its original name
so if you intend to reference this action by its name you'll need to run `->remember()` to store it under the new name.


#### Action variables

Actions may return a variable after being invoked. This value is shared back to the parent story's variable repository, using the action's variable name as the identifier for the variable.

By default, the variable name is the same as the action name, for example `action('do_something', fn () => 123)` will set the `do_something` variable to `123` once invoked against a story.

**Getting the variable name**

You can retrieve the current variable name by using the `->getVariable()` method:

```php
dump(
    action('do_something', fn () => 123)->getVariable()
);
// Dumps "do_something"
```

**Setting the variable name**

You may override the variable name using the `->variable()` method or the `variable` argument (in the constructor or `::make()` method), or by replacing the `variable` property in an Action class.

```php
$action = action('do_something', fn () => 123, variable: 'something');
dump($action->getVariable()); // Dumps "something"

$action = action('do_something', fn () => 123)->variable('something_else');
dump($action->getVariable()); // Dumps "something_else"

class MyAction extends Action
{
    protected string $variable = 'something_great';
}
dump(MyAction::make()->getVariable()); // Dumps "something_great"
```

**Recommendations and limitations**

- Avoid dots (`.`) in names.
  - Variable names with dots will be supported but will not support Dependency Injection (see "Action Callbacks" section below).
  - Variable names with dots will translate to nested variables using dot-notation, so `foo.bar` will be stored under `foo => [ bar => 'here' ]`.
- Use `camelCase` (or `snake_case`) names.
  - i.e. avoid spaces and special symbols.
  - Variable names with spaces or special symbols will be supported but will not support Dependency Injection (see "Action Callbacks" section below).

#### Action Callbacks

When ran, action callbacks are invoked with Laravel's container dependency injection - so you can retrieve any singleton or bound interface class you wish by utilising typed arguments.

Further to this, Pest Stories supports dependency injection with a few specific differences:

- `\BradieTilley\Stories\Story $story` yields the current story.
- `\PHPUnit\Framework\TestCase $test` yields the current test case (which you can typehint to your `Tests\TestCase` file if you're using an extended TestCase like in Laravel).
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
 * Dumps:
 * 
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