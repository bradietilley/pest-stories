[Documentation](/docs/documentation.md) > [Actions](/docs/actions.md) > Name

### Action Name

**Name**

All actions MUST have a name, which is provided when the action is created.

```php
Action::make('as_admin');

$action = new Action('do_something');
$action->store();

Story::make()
    ->action('as_admin')
    ->action('do_something');
```
