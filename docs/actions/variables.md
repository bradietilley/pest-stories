[Documentation](/docs/documentation.md) > [Actions](/docs/actions.md) > Variables

### Action Variables

> Only for scenarios (not tasks).

A scenario has a `$variable` property, which defaults to the `$name` property of the scenario when not manually specified. After the scenario's generator is invoked for a given story, the returned variable from the generator is then passed to the Story as a variable that's accessible later, via the variable key that the scenario has defined (`$variable` property).

The variable can be specified in 3 ways:

```php
// Default to scenario name
$a = Scenario::make('as_admin', function () {
    createAdminUser();

    return 'admin';
});

// Passed in constructor
$b = Scenario::make('as_admin', function () {
    createAdminUser();

    return 'admin';
}, 'role');

// Passed in variable() method
$c = Scenario::make('as_admin', function () {
    createAdminUser();

    return 'admin';
})->variable('chosen_role');

$a->getVariable(); // admin
$b->getVariable(); // role
$c->getVariable(); // chosen_role
```

See [Data / Variables](/docs/stories/data-variables.md) for more information on how data variables work.
