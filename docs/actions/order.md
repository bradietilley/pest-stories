[Documentation](/docs/documentation.md) > [Actions](/docs/actions.md) > Order

### Action Order

All actions have a boot "order", which allows you to create sequenced events, such as updating the authorised user _after_ acting as them.

The order may be specified in 3 ways:

```php
// 1) Defaulted (incremented each time, therefore order of creation = order of boot)
$a = Action::make('as_admin', fn () => null, 'var1');
$b = Action::make('as_admin', fn () => null, 'var2');

// 2) Passed in constructor
$c = Action::make('as_admin', fn () => null, 'var3', 5);

// 3) Passed in order() method
$d = Action::make('as_admin', fn () => null, 'var4')->order(4);

// 1) Default again (incremented from max order)
$e = Action::make('as_admin', fn () => null, 'var5');

$a->getOrder(); // 1
$b->getOrder(); // 2
$c->getOrder(); // 5
$d->getOrder(); // 4
$e->getOrder(); // 6
```

You may specify multiple actions or actions with the same order.
