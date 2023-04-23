# Pest Stories

A clean approach for writing large test suites.

![Static Analysis](https://github.com/bradietilley/pest-stories/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/pest-stories/actions/workflows/tests.yml/badge.svg)

### Installation

```
composer require bradietilley/pest-stories --dev
```

### Introduction

User stories are short, simple descriptions of a feature or functionality of a software application, typically written from the perspective of an end user.

Pest Stories is a PHP package that extends the PestPHP testing framework, allowing developers to write user stories in a clear and reusable way, making it easier to maintain and test their software applications.

### Documentation

Pest Stories can really boil down to a couple key areas:

- Actions
- Stories

##### Actions

Actions are typically short reusable code snippets that are designed to be used in various tests and they can represent either a small fragment of your overall system or a large pre-existing scenario that a test case must seed before running anything.

Adding actions to a test is extremely simple and operates in a similar way to other Pest features like arch testing.

In this example below we'll test a few areas of a card game that's built in Laravel:

```php

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
 * Throughout your feature tests you can re-use these actions
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

Alternatively you may define actions inline:

```php
test('can do something')
    ->action(function () {
        dump('do something here');
    });
```

Alternatively you may define actions using Action class names:

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

Alternatively you may define actions using pre-instantiated action classes:

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


