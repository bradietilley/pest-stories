[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Callbacks

### Story Callbacks

Inside storyboard there are many closure driven callbacks, such as Action (action and action) generators and registering/booting callbacks, story `before()` and `after()` callbacks, assertion `check()` callback, and even the `::actingAs()` callback.

Under the hood, all closure driven callbacks utilise Laravel's container dependency injection, and each closure driven callback will have access to each variable you have set against the story.

Note: There are a handful of variable names you will not be able to dependency inject (but can still use via get/set methods) as these are currently used by the plugin, including:

- `story` is reserved for the `Story` instance
- `test` is reserved for the `TestCase` instance
- `result` is reserved for the `Action` result(s) and is made available in the `after()` and assertion `check()` callbacks.
- `can` is reserved for the boolean flag specified via `can()` and/or `->cannot()` methdods.
- `user` is reserved for the Story user.

Examples:

```php
Action::make('an_action')->as(function (int $a, int $b) {
    echo $a; // 1
    echo $b; // 2
});

Story::make()
    ->before(function (int $a, int $b) {
        echo $a; // 1
        echo $b; // 2
    })
    ->action(function (int $a, int $b) {
        echo $a; // 1
        echo $b; // 2
    })
    ->after(function (int $a, int $b) {
        echo $a; // 1
        echo $b; // 2
    })
    ->assert(
        can: function (int $a, int $b) {
            echo $a; // 1
            echo $b; // 2
        },
        cannot: function (int $a, int $b) {
            echo $a; // 1
            echo $b; // 2
        },
    )
    ->set('a', 1)
    ->set('b', 2);
```

As mentioned in the [Action](/docs/actions.md) docs, each Action has a variable property, which defaults to the Action's name. The result of the Action generator (callback) is passed into the Story's variable data container via the `variable` key and can be later referenced using that key.

```php
Action::make('as_admin')
    ->as(function (Story $story) {
        $story->user(createAdmin());

        return 'admin';
    })
    ->variable('role')
    ->appendName();

Action::make('as_customer')
    ->as(function (Story $story) {
        $story->user(createCustomer());

        return 'customer';
    })
    ->variable('role')
    ->appendName();

Action::make('blocked')
    ->as(fn (Story $story) => $story->user->block())
    ->appendName();

Story::make('event log created when user is modified')
    ->assert(function (User $user, string $role, bool $blocked = false) {
        expectDatabaseExists('event_logs', [
            "User created with role `{$role}`",
        ]);

        if ($blocked) {
            expectDatabaseExists('event_logs', [
                "User #{$user->id} was blocked",
            ]);
        } else {
            expectDatabaseMissing('event_logs', [
                "User #{$user->id} was blocked",
            ]);
        }
    })
    ->can()
    ->stories([
        Story::make()->action('as_admin'),
        Story::make()->action('as_admin')->action('blocked'),
        Story::make()->action('as_customer'),
        Story::make()->action('as_customer')->action('blocked'),
    ]);

/**
 * [Can] event log created when user is modified as admin
 * [Can] event log created when user is modified as admin blocked
 * [Can] event log created when user is modified as customer
 * [Can] event log created when user is modified as customer blocked
 */
```