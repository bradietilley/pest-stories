[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Callbacks

### Story Callbacks

Inside storyboard there are many closure driven callbacks, such as Action (scenario and task) generators and registering/booting callbacks, story `before()` and `after()` callbacks, assertion `check()` callback, and even the `::actingAs()` callback.

Under the hood, all closure driven callbacks utilise Laravel's container dependency injection, and each closure driven callback will have access to each variable you have set against the story.

Note: There are a handful of variable names you will not be able to dependency inject (but can still use via get/set methods) as these are currently used by the plugin, including:

- `story` is reserved for the `Story` instance
- `test` is reserved for the `TestCase` instance
- `result` is reserved for the `Task` result(s) and is made available in the `after()` and assertion `check()` callbacks.
- `can` is reserved for the boolean flag specified via `can()` and/or `->cannot()` methdods.
- `user` is reserved for the Story user.

Examples:

```php
Scenario::make('a_scenario')->as(function (int $a, int $b) {
    echo $a; // 1
    echo $b; // 2
});

Task::make('a_task')->as(function (int $a, int $b) {
    echo $a; // 1
    echo $b; // 2
});

Story::make()
    ->scenario('a_scenario')
    ->scenario(function (int $a, int $b) {
        echo $a; // 1
        echo $b; // 2
    })
    ->before(function (int $a, int $b) {
        echo $a; // 1
        echo $b; // 2
    })
    ->task('a_task')
    ->task(function (int $a, int $b) {
        echo $a; // 1
        echo $b; // 2
    })
    ->after(function (int $a, int $b) {
        echo $a; // 1
        echo $b; // 2
    })
    ->check(
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

As mentioned in the [Action](/docs/actions.md) docs, each Scenario has a variable property, which defaults to the Scenario's name. The result of the Scenario generator (callback) is passed into the Story's variable data container via the `variable` key and can be later referenced using that key.

```php
Scenario::make('as_admin')
    ->as(function (Story $story) {
        $story->user(createAdmin());

        return 'admin';
    })
    ->variable('role')
    ->appendName();

Scenario::make('as_customer')
    ->as(function (Story $story) {
        $story->user(createCustomer());

        return 'customer';
    })
    ->variable('role')
    ->appendName();

Scenario::make('blocked')
    ->as(fn (Story $story) => $story->user->block())
    ->appendName();

Story::make('event log created when user is modified')
    ->check(function (User $user, string $role, bool $blocked = false) {
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
        Story::make()->scenario('as_admin'),
        Story::make()->scenario('as_admin')->scenario('blocked'),
        Story::make()->scenario('as_customer'),
        Story::make()->scenario('as_customer')->scenario('blocked'),
    ]);

/**
 * [Can] event log created when user is modified as admin
 * [Can] event log created when user is modified as admin blocked
 * [Can] event log created when user is modified as customer
 * [Can] event log created when user is modified as customer blocked
 */
```