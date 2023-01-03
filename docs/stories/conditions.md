[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Conditions

### Story Conditions

Stories use Laravel's `\Illuminate\Support\Traits\Conditionable` trait, meaning you can easily add chainable conditions to your stories.

```php
StoryBoard::make()
    ->name('do something')
    ->when($boolCondition, fn (Story $story) => $story->scenario('some_scenario'))
    ->test();
```