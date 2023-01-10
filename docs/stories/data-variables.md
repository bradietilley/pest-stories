[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Data / Variables

### Story Data / Variables

You may store variables in a Story using the data container.

**Setting a variable**

This can be achieved via the `set()` and `setData()` methods. Example:

```php
$story = Story::make()
    ->cannot()
    ->set('expectedErrors', [ 'email' => 'The email must be unique.' ])
    ->set('payload', [ 'email' => User::first()->email, ])
    ->set([
        'expectedValid' => 'password',
    ]);
```

**Getting a variable**

This can be achieved via the `get()` and `getData()` methods. Example:

```php
Story::make()
    ->action('as_admin')
    ->action(function (Story $story, TestCase $test, array $payload) {
        // Get via dependency injection
        $test->post('/users/', $payload);
    })
    ->cannot()
    ->assert(
        cannot: function (Story $story, TestCase $test) {
            // Get via story
            $invalid = $story->get('expectedErrors');
            $test->assertInvalid($invalid);
        },
    )
    ->stories([
        ...
    ]);
```

**Checking a variable**

You can check for the existence of a variable via the `has()` and `hasData()` methods. Example:

```php
Story::make()
    ->action(function (Story $story) {
        if ($story->has('expectedErrors')) {
            // do something
        } else {
            // do something else
        }
    })
    ->stories([
        ...
    ]);
```

**All Data**

You can get all data via the `all()` and `allData()` methods. Example:

```php
$story = Story::make()->set('a', 1)->set('b', 2);
$story->all();

/**
 * Output:
 * 
 * [
 *   'a' => 1,
 *   'b' => 2,
 * ]
 */
```

**Data Injection**

As depicted in the `get` examples above, you may also pluck variables from the data container by accepting them in any closure-driven callback. See [Callbacks](/docs/stories/callbacks.md) for more information.