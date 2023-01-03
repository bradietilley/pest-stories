[Documentation](/docs/documentation.md) > [Actions](/docs/actions.md) > Order

### Action Order

All actions have a boot "order" which is vital to create sequenced events, such as creating a user before acting as them.

The order may be specified in 3 ways:

```php
// Defaulted (incremented each time, therefore order of creation = order of boot)
$a = Scenario::make('as_admin', fn () => null, 'var1');
$b = Scenario::make('as_admin', fn () => null, 'var2');

// Passed in constructor
$c = Scenario::make('as_admin', fn () => null, 'var3', 5);

// Passed in order() method
$d = Scenario::make('as_admin', fn () => null, 'var4')->order(4);

// Default Again (incremented from max order)
$e = Scenario::make('as_admin', fn () => null, 'var5');

$a->getOrder(); // 1
$b->getOrder(); // 2
$c->getOrder(); // 5
$d->getOrder(); // 4
$e->getOrder(); // 6
```

You may specify multiple tasks or scenarios with the same order.
