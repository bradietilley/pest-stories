[Documentation](/docs/documentation.md) > [Actions](/docs/actions.md) > Test Name

### Action Test Name

By default, actions don't modify the name of a Story, so when you specify a story (that has a name) with a action, only the story name gets printed. For example, typical story naming is like so:

```php
Story::make('do something cool')->run();
// Name: do something cool
```

You may wish to automatically suffix a bit of text to the Story name whenever the action is added to a story. This can be achieved via the `->appendName()` method in one of two ways:

```php
// 1) Use the action name (in sentence case without underscores)
Action::make('as_admin')->appendName();

// 2) Use a custom name
Action::make('without_2fa')->appendName('without Two-Factor');

// Example name inheritance:
Story::make('create something')
    ->can()
    ->action(fn (User $user) => (new PostPolicy())->create($user))
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

You may utilise this feature only when the action is created as an Action class such as when you `Action::make()` or even `new Action()`, even when you reference the action by name in the story like used above (`->name('my_action')`).

However, it will not work when using inline closure actions like

```php
Story::make('story name')
    ->action(function () {
        doSomething();
    });

// Name: story name
```
