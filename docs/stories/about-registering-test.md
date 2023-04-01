# [Stories](/docs/stories/README.md) > Registering Tests

Once you've fleshed out your stories you'll need to register the test with Pest.

### Registering a single Story test

To register the test, simply run `$story->test()`. For example:

```php
$story = story('a single story')
    ->action(fn () => doSomething())
    ->assertion(fn () => expectSomething())
    // Register the test case
    ->test();
```

Under the hood, this passes the Story object to Pest's `test` function as if you were to manually specify the following:

```php
test($story->getTestName(), fn () => $story->process());
```

This yields the following test:

```
> a single story
```

### Registered nested Story tests

The interface for registering stories is unchanged for nested stories, however under the hood it operates a little differently. Take the following for example:


```php
$story = story('a parent story')
    ->action(fn () => doSomething())
    ->assertion(fn () => expectSomething())
    ->stories([
        story('a'),
        story('b'),
        story('c')->stories([
            story('1'),
            story('2'),
            story('3'),
        ]),
    ])
    // Register the test case
    ->test();
```

Under the hood, it grabs every child-most Story object (a, b, 1, 2, 3) and uses those stories as a dataset for the parent story.

This yields the following tests:

```
> a parent story with dataset "a"
> a parent story with dataset "b"
> a parent story with dataset "c 1"
> a parent story with dataset "c 2"
> a parent story with dataset "c 3"
```

See [Naming Conventions](/docs/stories/about-naming-conventions.md) for more information on how to achieve your desired test names.
