[Documentation](/docs/documentation.md) > [Actions](/docs/actions.md) > Name

### Action Name

All actions MUST have a name, which is provided when the action is created.

```php
// Static constructor (stores it in the repository)
Action::make('as_admin')->as(fn (Story $story) => $story->user(createAdmin()));

// Standard constructor (doesn't store in the repository)
$action = new Action('do_something');
// Standard constructor: you may store it in the repository manually
$action->store();

// You can reference statically constructed/stored actions via their name:
Story::make()
    ->action('as_admin')
    ->action('do_something');
```
