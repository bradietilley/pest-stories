[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Actions

### Story Actions (Tasks + Actions)

See [Actions](/docs/actions.md) for underlying documentation to Actions, Tasks and Actions. This documentation refers to how to utilise an action in a story.

**Adding to stories**

You can add actions and tasks to a story in a few ways:

```php
$action = Action::make('action_a')->as(fn () => doSomething());
$task = Task::make('task_a')->as(fn () => doSomethingElse());

// By name
Story::make()->action('action_a');

// By variable / instance
Story::make()->action($action);

// By closure
Story::make()->action(fn () => doSomething());
```

Note: when using closures to add actions to a story, the underling 'name' is defaulted to 'inline_{HASH}'.

You may also supply actions en-masse:

```php
Action::make('action_a', ...);
Action::make('action_c', ...);

// Variable arguments
Story::make()->actions('action_a', Action::make('action_b', ...));

// Array argument
Story::make()->actions([
    'action_c',
    Action::make('action_d', ...),
]);

// Combination

Story::make()->actions(
    'action_a',
    Action::make('action_b', ...),
    [
        'action_c',
        Action::make('action_d', ...),
    ],
);
```
