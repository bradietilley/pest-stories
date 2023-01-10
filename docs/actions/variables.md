[Documentation](/docs/documentation.md) > [Actions](/docs/actions.md) > Variables

### Action Variables

All actions have a `$variable` property -- when not provided, it defaults to the `$name` property of the action.

After the action's generator is invoked for a given story, the value the generator returns is then passed to the Story as a variable that's accessible later.

The variable can be specified in 3 ways:

```php
// 1) Default to action name
$a = Action::make('as_admin', function () {
    createAdminUser();

    return 'admin';
});

// 2) Passed in constructor
$b = Action::make('as_admin', function () {
    createAdminUser();

    return 'admin';
}, 'chosen_role');

// 3) Passed in variable() method
$c = Action::make('as_admin', function () {
    createAdminUser();

    return 'admin';
})->variable('role');

// Get the example (would rarely ever need to)
$a->getVariable(); // admin
$b->getVariable(); // role
$c->getVariable(); // chosen_role

Story::make()
    ->action('as_admin') // This would be the `$c` as_admin action, by the way
    ->action(function (string $role) { // and this would be computed second based on order of definition
        echo $role; // admin
    });
```

See [Data / Variables](/docs/stories/data-variables.md) for more information on how data variables work.
