[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Inheritance

### Story Inheritance

Story elements can be nested as much as you need. Parent stories share certain attributes with its children, or more simply, children inherit certain attributes from their parents.

You may nest stories via the `->stories()` method, like so:

```php
Story::make('this is a parent story')->stories([
    Story::make('this is a child story'),
    Story::make('this is another child story'),
]);
```

See the relevant documentation pages to see what attributes support inheritance.