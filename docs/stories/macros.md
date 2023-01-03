[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Macros

### Story Macros

Stories use Laravel's `\Illuminate\Support\Traits\Macroable` trait, meaning you can easily add your own custom macros to your stories.

```php
Story::macro('asAdmin', function () {
    /** @var Story $this */
    $this->scenario(function (Story $story) {
        $story->user(createAdmin());

        echo "Scenario run";
    });
    
    echo "Macro run";

    return $this;
});

StoryBoard::make()
    ->can()
    ->name('do something as admin')
    ->asAdmin()
    ->task(fn () => auth()->user()->isAdmin())
    ->check(fn (bool $result) => expect($result)->toBeTrue())
    ->test();

echo "Story created";
```

Output:

```
Macro run
Story created
Scenario run
```