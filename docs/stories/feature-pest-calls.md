# [Stories](/docs/stories/README.md) > Pest Calls

You're probably familiar with Pest's `TestCall` helper methods like `->skip()`, `->todo()`, `->throws()`, etc.

In Pest Stories, these exact same helper methods are available on any given `Story` object. When you call it on a story, it remembers it, then applies it on the `TestCall` object derived from Pest's `test()` once the `Story` is registed.

The only way this differs from standard Pest is that it's inheritable.

## Usage

```php
story('can do something')
    ->action('do_something')
    ->assertion('could_do_something')
    ->stories([
        story('as a super admin')->action('as_super_admin'),
        story('as an admin')->action('as_admin'),
        story('as a user')->action('as_user'),
    ])
    ->todo()
    ->test();
```

The `todo` will be applied to all 3 stories:

>>> can do something as a super admin (TODO)
>>> can do something as an admin (TODO)
>>> can do something as a user (TODO)
