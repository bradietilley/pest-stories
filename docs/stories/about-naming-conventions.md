# [Stories](/docs/stories/README.md) > Naming Conventions

All `Callback` classes must have a name provided at time of construction.

The name can be set on construct and retrieved via the `->getName()` method.

```php
$action = action('do_something');

echo $action->getName(); // do_something
```

This name is what should be used when fetching the callback by name. For example:

```php
$action = action('do_something_great');

Action::fetch('do_something_great') === $action; // true
```

#### Name Inheritance

For `Story` classes, the naming convention changes a bit. If a parent story has a name, it will pass that name to it children as a prefix. For example:

```php
story('a parent story')
    ->stories([
        story()->stories([
            story('child a'),
        ]),
        story('child 2')->stories([
            story('child b'),
        ]),
    ]);

/**
 * The two stories that will get run are those with no children, so child a and b. Their names:
 * 
 *    - a parent story child a
 *    - a parent story child 2 child b
 */
```

#### Append variables to name


```php
story('can do something')
    ->append('value')
    ->set('more', 'x')
    ->stories([
        story('child 1')->set('value', 'a'),
        story('child 2')->set('value', 'b'),
        story('child 3')->set('value', 'c')->append('more'),
    ]);

/**
 * The three stories that will get run are those with no children, so child 1, 2 & 3. Their names:
 * 
 *     - can do something value: a
 *     - can do something value: b
 *     - can do something value: c, more: x
```
