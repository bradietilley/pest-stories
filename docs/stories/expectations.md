[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Expectations

### Story Expectations

Each story must have an expectation, whether it's defined on the child story or inherited from one of its ancestors.

An expectation is simply a _You can do this_ (`->can()`) or a _You cannot do this_ (`->cannot()`), and is helpful for any binary-based checks i.e. you can or cannot create or view a record.

Example:

```php
Story::make('create something')
    ->stories([
        Story::make()->action('as_admin')->can(),
        Story::make()->action('as_customer')->cannot(),
    ]);

/**
 * Expectation is that you can create something as an admin,
 * but cannot create something as a customer.
 */
```

You may pass an expectation to a parent and it will be inherited by the child, unless overwritten:

```php
Story::make('create something')
    ->cannot()
    ->stories([
        Story::make()->action('as_admin')->can(),
        Story::make()->action('as_customer'),
    ]);
```

The expectations modify the name of the story by offering a `[Can]` or `[Cannot]` prefix, allowing you to easily read the expectation from unit test names. Examples:

```
[Can] create something as admin
[Cannot] create something as customer
```

See [Expectation Names](/docs//stories/name.md#expectation-names) for more information.