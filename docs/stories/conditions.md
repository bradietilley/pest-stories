[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Conditions

### Story Conditions

Stories use Laravel's `\Illuminate\Support\Traits\Conditionable` trait, meaning you can easily add chainable conditions to your stories.

```php
Story::make()
    ->name('do something')
    ->when($boolCondition, fn (Story $story) => $story->action('some_action'))
    ->test();
```

See Laravel's [Conditionable](https://laravel.com/api/9.x/Illuminate/Support/Traits/Conditionable.html) documentation for more information.