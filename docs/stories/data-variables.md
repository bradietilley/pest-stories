[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Data / Variables

### Story Data / Variables

You may store variables in a Story via the data container.

**Setting a variable**

This can be achieved via the `set()` and `setData()` methods. Example:

```php
$story = Story::make()
    ->cannot()
    ->set('expectedErrors', [ 'email' => 'The email must be unique.' ])
    ->set('payload', [ 'email' => User::first()->email, ]);
```

**Getting a variable**

This can be achieved via the `get()` and `getData()` methods. Example:

```php
Story::make()
    ->action('as_admin')
    ->action(fn (Story $story, TestCase $test) => $test->post('/users/', $story->get('payload')))
    ->cannot()
    ->assert(
        cannot: function (Story $story, TestCase $test) {
            $test->assertInvalid($story->get('expectedErrors'))
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

See [Callbacks](/docs/stories/callbacks.md) for more information.