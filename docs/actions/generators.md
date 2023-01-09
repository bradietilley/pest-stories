[Documentation](/docs/documentation.md) > [Actions](/docs/actions.md) > Generators

### Action Generators

All actions MUST have a "generator" which is a callback that is executed when the action is booted for a given story. The generator can be provided in two ways:

```php
Action::make('as_admin', function () {
    // sign in to admin
});

// or

Action::make('as_admin')->as(function () {
    // sign in to admin
});
```

See [Workflow](/docs/stories/workflow.md) for more information on exactly when actions are booted.
See [Callbacks](/docs/stories/callbacks.md) for more information on the available arguments for the closures.
