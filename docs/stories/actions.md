[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Actions

### Story Actions (Tasks + Scenarios)

See [Actions](/docs/actions.md) for underlying documentation to Actions, Tasks and Scenarios. This documentation refers to how to utilise an action in a story.

**Adding to stories**

You can add scenarios and tasks to a story in a few ways:

```php
$scenario = Scenario::make('scenario_a')->as(fn () => doSomething());
$task = Task::make('task_a')->as(fn () => doSomethingElse());

// By name
Story::make()->scenario('scenario_a');
Story::make()->task('task_a');

// By variable / instance
Story::make()->scenario($scenario);
Story::make()->task($task);

// By closure
Story::make()->scenario(fn () => doSomething());
Story::make()->task(fn () => doSomethingElse());
```

Note: when using closures to add actions to a story, the underling 'name' is defaulted to 'inline_{HASH}'.

You may also supply actions en-masse:

```php
Scenario::make('scenario_a', ...);
Scenario::make('scenario_c', ...);

// Variable arguments
Story::make()->scenarios('scenario_a', Scenario::make('scenario_b', ...));

// Array argument
Story::make()->scenarios([
    'scenario_c',
    Scenario::make('scenario_d', ...),
]);

// Combination

Story::make()->scenarios(
    'scenario_a',
    Scenario::make('scenario_b', ...),
    [
        'scenario_c',
        Scenario::make('scenario_d', ...),
    ],
);
```
