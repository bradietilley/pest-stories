[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Actions

### Story Actions

See [Actions](/docs/actions.md) for underlying documentation to Actions. This documentation refers to how to utilise an action in a story.

**Adding to stories**

You can add actions to a story in a few ways:

```php
// 1) Creating an action
$action = Action::make('action_a')->as(fn () => doSomething());

// 1) Pass it in by variable / instance
Story::make()->action($action);

// 2) By name
Story::make()->action('action_a');

// 3) By closure (i.e. inline)
Story::make()->action(fn () => doSomething());
```

Note: when using closures to add actions to a story, the underling 'name' is defaulted to 'inline_{HASH}' and therefore cannot (can; but not easily) be reused in other stories.

You may also supply actions en-masse:

```php
Action::make('action_a', [ 'arg' => 1, ... ]);
Action::make('action_c', [ 'arg' => 1, ... ]);

// 1) Variable arguments
Story::make()->actions('action_a', Action::make('action_b', [ 'arg' => 1, ... ]));

// 2) Array argument
Story::make()->actions([
    'action_c',
    Action::make('action_d', [ 'arg' => 1, ... ]),
]);

// 3) Combination
Story::make()->actions(
    'action_a',
    'action_b' => [ 'arg' => 1, ... ],
    [
        'action_c',
        Action::make('action_d', [ 'arg' => 1, ... ]),
    ],
);
```