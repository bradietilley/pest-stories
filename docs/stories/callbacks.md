[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Callbacks

### Story Callbacks

Inside Pest StoryBoard there are many closure-driven callbacks, such as:

- Story:
    - Action(s):
        - `->registering($callback)`
        - `->booting($callback)`
        - `->as($callback)` _See [Action Generators](/docs/actions/generators.md) for more info._
    - `->setUp($callback)`
    - `->before($callback)`
    - `->after($callback)`
    - `->tearDown($callback)`
    - `->assert($callbackCan, $callbackCannot)` _See [Assertions](/docs/stories/assertions.md) for more info._
    - `::actingAs($callback)` _See [User / Acting As](/docs/stories/users-acting-as.md) for more info._

Under the hood, all closure-driven callbacks utilise Laravel's container dependency injection, with a few extra parameters you may accept in your closures.

Note: There are a handful of variable names you will not be able to dependency inject (but can still use via get/set methods) as these are currently used by Pest StoryBoard, including:

- `story` is reserved for the `Story` instance
    - This is available across any callback.
- `test` is reserved for the `TestCase` instance
    - Only available when story is running when `->test()` registers the story test
    - See [Workflow / Testing](/docs/stories/workflow-testing.md) for more info.
- `result` is reserved for the `Action` result(s)
    - Only available in action `as()` generators, and story `after()` and `assert()` callbacks.
    - See [Assertions](/docs/stories/assertions.md) for more info.
- `can` is reserved for the boolean flag specified via `can()` and/or `->cannot()` methdods.
    - See [Assertions](/docs/stories/assertions.md) for more info.
- `user` is reserved for the Story user.
    - See [Users / Acting As](/docs/stories/users-acting-as.md) for more info.

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
        // createAdmin should create a user and thus record event_logs
        $story->user(createAdmin());

        return 'admin';
    })
    ->variable('role')
    ->appendName();

Action::make('as_customer')
    ->as(function (Story $story) {
        // createCustomer should create a user and thus record event_logs
        $story->user(createCustomer());

        return 'customer';
    })
    ->variable('role')
    ->appendName();

Action::make('blocked')
    ->as(function (Story $story) {
        // block should record event_log
        $story->user->block();
    })
    ->appendName();

Story::make('event log created when user is modified')
    ->before(function (string $role) {
        // Before we start we should not have any event logs with this name
        expectDatabaseMissing('event_logs', [
            'message' => "User created with role `{$role}`",
        ]);
    })
    ->assert(function (User $user, string $role, bool $blocked = false) {
        expectDatabaseExists('event_logs', [
            'message' => "User created with role `{$role}`",
        ]);

        if ($blocked) {
            expectDatabaseExists('event_logs', [
                'message' => "User #{$user->id} was blocked",
            ]);
        } else {
            expectDatabaseMissing('event_logs', [
                'message' => "User #{$user->id} was blocked",
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