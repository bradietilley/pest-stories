[Documentation](/docs/documentation.md) > [Actions](/docs/actions.md) > Test Name

### Action Test Name

By default, actions don't modify the name of a Story, so when you specify a story with a name and a action or task, only the story name gets printed.

However, you may opt to suffix a bit of text to the Story name whenever the action or task is added. This can be achieved via the `appendName()` method in one of two ways:

```php
// Use the action name (in sentence case without underscores)
Action::make('as_admin')->appendName();

// Use a custom name
Action::make('without_2fa')->appendName('without Two-Factor');

// Example name inheritance:
Story::make('create something')
    ->can()
    ->task(fn () => null)
    ->stories([
        Story::make()->action('as_admin'),
        Story::make()->action('as_admin')->action('without_2fa'),

        Story::make('very cool')->action('as_admin'),
        Story::make('very cool')->action('as_admin')->action('without_2fa'),
    ]);

/**
 * The four story names:
 * 
 * [Can] create something as admin
 * [Can] create something as admin without Two-Factor
 * [Can] create something very cool as admin
 * [Can] create something very cool as admin without Two-Factor
 */
```

Note: You may utilise this feature only when the action is instantiated as an instance (at some point), such as when you `Task::make()` or even `new Action()` -- even when you reference the action by name in the story. It will not work when using closure actions like `->action(fn () => doSomething())` or `->task(fn () => doSomething())`