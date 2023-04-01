# [Stories](/docs/stories/README.md) > Variables / Data Repository

Throughout the lifecycle of a `Story`, you can set and get variables. This is helpful for many scenarios, one scenario being when you need to create a resource in an action, then reference it in an assertion.

## Usage

#### Set a variable

```php
$story = story()
    ->set('seed', mt_rand(1000, 9999))
    ->with([
        'altSeed' => mt_rand(1000, 9999),
    ]);

/**
 * Example:
 * 
 * [
 *     'seed' => 1234,
 *     'altSeed' => 5678,
 * ],
 */
```

#### Get a variable

```php
// Get the seed variable or 0 if not set
echo $story->get('seed', 0); // 1234
```

#### Callbacks

Any `Closure` in a `Callback` object that is run after the application is booted (i.e. anything after or incl `setUp`) will be invoked with Laravel's container / DI facility and with this, any variable you define in the story prior to the `Closure` invocation will be made available.

For example:

```php

story('can do something')
    ->set('abc', 123)
    ->action(function (int $abc) {
        $this->set('def', 456);
    })
    ->assertion(fn (int $def) => expect($def)->toBe(456))
    ->test();
```

#### Action/Assertion Variables

Actions and Assertions have their own variable / data repository, made available only to their lifecycle, allowing you to reuse the same variable names for each action without collision.

Actions and Assertions also have a variable name - this is by default the object's name and can be overridden using the `->for('newVarName')` method.

When an Action or Assertion is booted on a Story, the value returned is stored in the Story's variable/data repository and can be shared with other subsequent actions, assertions or closure callbacks (e.g. after, expect, tearDown, etc).