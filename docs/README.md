# Pest Stories Documentation

Pest Stories can really boil down to a couple key areas:

- [Stories](#stories)
  - [Creating Stories](#creating-stories)
  - [Data Repository](#data-repository)
- [Actions](#actions)
  - [Creating Actions](#creating-actions)
  - [Adding Actions to Stories](#adding-actions)
  - [Alternative Syntax](#alternative-syntax)
  - [Naming](#naming-actions)
  - [Variables](#variables)
  - [Dependency Injection](#dependency-injection)
  - [Nesting Actions](#nested-actions)
  - [Deferring Actions](#deferring-actions)

## Stories

When you create a Pest Story a pending `Story` object is created. Throughout the lifecycle of the story - from the test's set up to the test's tear down - this story is available, namely to every action that gets invoked against it. The story acts as a hub for holding data returned from each action and sharing these key pieces of information to each subsequent action thereafter.

### Creating Stories

You can create a Story test using the following syntax:

```php

test('your test case name')
    ->action()->...;

// or

test('you test case name 2')
    ->story()->...;
```

### Data Repository

You can get, set, and check the existence of variables against a story.

The data repository is powered by `\Illuminate\Support\Arr::` so dot notation is supported.

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

## Actions

Actions are typically short reusable code snippets that are designed to be used in various tests and they can represent either a small fragment of your overall system or a large pre-existing scenario that a test case must seed before running anything.

#### Creating Actions

Creating actions is easy and flexible. Actions can be defined in a `*Test.php` file, or in the parent `Pest.php` file, or in a separately loaded `Actions.php` file -- your choice.

In this example below we'll create actions that configure scenario(s) for a card game that's built in Laravel, and a couple assertion-based actions:

```php
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
```

As the above demonstrates, you can create actions in a similar way to how you'd normally create a pest `test` using the `test($name, $callback)` syntax.

Continue reading below to see what to do with these actions.


#### Adding Actions

Adding actions to a test is extremely simple and operates in a similar syntax to other Pest features like arch testing.

In this example below we'll utilise the actions above in a few tests:

```php
use BradieTilley\Stories\Helpers\action;

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

As the above demonstrates, you can add these actions to a test by referencing the name that was given to each action.

#### Alternative Syntax

**Using name identifiers:**

As demonstrated above, a basic way to creating resuable actions is by using the `BradieTilley\Stories\Helpers\action` function. You can either provide the closure callback as part of the `action()` arguments, or by chaining on `->as(fn () => doSomething())`

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

Sometimes your action may be so unique that it may not be worth creating a reusable action, so in these cases you can instead define them as inline closures. This closure can operate just like a standard test callback that you're familiar with, but allows for chaining of other actions before and/or after.

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

Once created, you can add the action to a story/test by passing in the underlying `$name` property (if specified), or by passing in the namespace of the Action class.

See below and the "Using instantiated action classes" section below as a couple examples:

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

Carrying on from the above, you may wish to instantiate the `Action` class so that you can give the action some context or override values that the action interacts with.

The `::make()` method accepts any number of arguments that are passed directly to the `__construct`, which you may override if need be. *Note: For custom action classes you do not need to call `parent::__construct()` from your construct. Calling the parent construct will set the name, variable and callback, if any are provided, and will remember the Action in the Actions repository*

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

<a id="docs-actions-naming"></a>

#### Naming Actions

It's recommended to name each action in a way that is readable almost like a user story, but there's no requirement for this.

An action created using the `action` helper function will accept (but not require) a name as the first parameter. This allows the action to be added to stories/tests using the same name that was provided, as described in the various examples above.

**Default names**

By default if you provide no name then a random name will be given, meaning you'll be unable to reference it by name (unless you inspect the Actions repository and figure out which action was recently added).

Any class-based Action that does not have an explicit `$name` property specified will be given a random name that's prefixed with the namespace of the class, for example an action class with the namespace `Tests\Actions\CanViewModel` will be given a name like `Tests\Actions\CanViewModel@1n4rf90d`.

**Overriding names**

The name can be overridden after-the-fact using the `->name($name)` method. Note: The `Actions` repository may have already recorded it under its original name
so if you intend to reference this action by its new name then you'll need to run `->remember()` to store it under the new name.

<a id="docs-actions-variables"></a>

#### Variables

Actions may return a value when invoked. This value is shared back to the parent story's data repository, using the action's variable name as the identifier for the variable.

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

As described in the [Stories > Data Repository](#stories-data-repo) section, dot notation is supported. Because of this, there's a few naming conventions you should aim for and/or avoid.

- Avoid dots (`.`) in names unless you wish for something to be nested.
  - Variable names with dots will be supported but will not support Dependency Injection (see [Dependenct Injection](#actions-di) section below).
  - Variable names with dots will translate to nested variables using dot-notation, so `foo.bar` will be stored under `foo => [ bar => 'here' ]`.
- Use `camelCase` (or `snake_case`) names.
  - i.e. avoid spaces and special symbols.
  - Variable names with spaces or special symbols will be supported but will not support Dependency Injection (see "Action Callbacks" section below).


#### Dependency Injection

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

#### Deferring Actions

When you add an action by its name, the computation of the action is deferred by default as the entire `->action('do_something')` method call is deferred by Pest until the Test Case is invoked.

The execution of any logic inside the argument list is run immediately however (basic PHP stuff). In the above example of `->action('do_something')`, the string literal of `'do_something'` is in fact immediately calculated, but for a plain string this is not really relevant, as no computing really occurs, but of course if this was a concentation of strings it'd be calculated immediately -- and this is where you may need to handle things differently.

So it's worth nothing that when you add an action by instance (`->action(MyAction::make())`), the `::make()` method is immediately computed, meaning the `__construct` is immediately computed and any chained methods (e.g. `MyAction::make()->withSomething()`) are also immediately computed. If any logic in these methods rely on a booted Laravel application (i.e. they reference the API, DB, session, etc) then it will cause issues.

In these cases, it's recommended to use the `::defer()` method instead of the `::make()` method, which will defer the entire construction and configuration (chained methods) of the action until the test case runs the action, allowing you to reference the application (API, database, sessions, etc) directly in these configuration (chained) methods.

There's no real need to immediate invoke logic in these setup methods, except to reduce the code complexity as there'd be no storing of properties that requires you to later reference those properties in the `__invoke`  method and run everything in there. I'm not going to tell
you how to code something, so if you need to achieve something like the example below, `::defer()` will help you. 

```php
class AsUser extends Action
{
    public function admin(): static
    {
        actingAs(User::factory()->create([
            'is_admin' => true,
        ]));

        return $this;
    }
}

test('can do something as an admin')
    ->action(AsUser::make()->admin()); // application/DB not booted - error

test('can do something as an admin')
    ->action(AsUser::defer()->admin()); // works as you'd expect.
```

Deferring is not the default behaviour as you can defer logic when it's immediately run, but you cannot immediately run code when the entire chain is deferred.

Should you not want to continually remember which action classes require immediate computation vs those that require deferred computation, you can add the `BradieTilley\Stories\Contracts\Deferred` interface to any action that must be deferred. This will replace the result from the `::make()` method (normally instance of `Action`) with a `PendingCall<Action>` instance, simulating the exact behaviour of `::defer()`. This then allows you to use `::make()` on *all* actions without having to remember these finicky differences.

#### Datasets in Actions

Datasets behave slightly differently in Stories or, specifically, Actions. From the outset, the syntax is no different, but how your arguments are provided to you differs.

In standard Pest or PHPUnit the dataset arguments are simply passed in from left-to-right.

```php
test('a plain pest test', function (string $word, int $number) {
    dump($word, $number);
})->with([
    'custom subtext 1' => [ 'foo', 123 ],
    'custom subtext 2' => [ 'bar', 456 ],
]);

/**
 * >> a plain pest test with dataset "custom subtext 1"
 * dumps: foo
 * dumps: 123
 * 
 * >> a plain pest test with dataset "custom subtext 2"
 * dumps: bar
 * dumps: 456
 */
```

In Pest actions, because Dependency injection allows for the injection of various other variables including the Story or TestCase ([read more](#dependency-injection)), we have to meet in the middle and agree that when we want a dataset to be provided to a specific action, the first *n* arguments represent the *n* dataset arguments.

Not all actions have to support the dataset, it can just be one - or none. You can also retrieve the dataset verbatim using the `story()->getDataset()` method.

To enable automatic injection of all dataset arguments into a single action, you must run the `->dataset()` method on the action. For example:

```php
action('do_something', function (string $word, int $number) {
    dump($word, $number);
})->dataset();

test('a story pest test')
    ->action('do_something')
    ->with([
        'custom subtext 1' => [ 'foo', 123 ],
        'custom subtext 2' => [ 'bar', 456 ],
    ]);

/**
 * >> a plain pest test with dataset "custom subtext 1"
 * dumps: foo
 * dumps: 123
 * 
 * >> a plain pest test with dataset "custom subtext 2"
 * dumps: bar
 * dumps: 456
 */
```

In the above example, you can still utilise the [dependency injection](#dependency-injection) within the action arguments however they must occur **after** the dataset arguments. So for example if you wanted an existing variable from another action as well as dataset arguments, you'd need to go: `function ($datasetOne, $datasetTwo, $existingVariable1, $existingVariable2) { ... }`
