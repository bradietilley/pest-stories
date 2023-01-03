[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Expectations

### Story Expectations

Each story must have an expectation - whether it's defined on the child story or inherited from one of its ancestors.

The expectation is helpful for any binary-based checks i.e. you can or cannot do something. To add an expectation to a story, use the `->can()` or `->cannot()` methods.

Example:

```php
Story::make('create something')->can();
Story::make('delete something')->cannot();
```

You may pass an expectation to a parent and it will be inherited by the child, unless overwritten:

```php
Story::make('parent')->can()->stories([
    Story::make('child 1'),           // can
    Story::make('child 2')->cannot(), // cannot
]);
```

The expectations modify the name of the story by offering a `[Can]` or `[Cannot]` prefix, allowing you to easily read the expectation from unit test names.

See [Expectation Names](/docs//stories/name.md#expectation-names) for more information.