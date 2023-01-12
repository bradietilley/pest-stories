[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Macros

### Story Macros

Stories use Laravel's `\Illuminate\Support\Traits\Macroable` trait, meaning you can easily add your own custom macros to your stories.

```php
Action::make('as_admin')->as(function (Story $story) {
    $story->user(createAdmin());

    echo "Action run";
});

// Create an asAdmin method for Story that registers an action
Story::macro('asAdmin', function () {
    /** @var Story $this */

    $this->action('as_admin');
    
    echo "Macro run";

    return $this;
});

Story::make()
    ->can()
    ->name('do something as admin')
    ->asAdmin()
    ->before(fn () => echo 'Before actions')
    ->action(fn () => auth()->user()->isAdmin())
    ->assert(fn (bool $result) => expect($result)->toBeTrue())
    ->test();

echo "Story created";
```

Output:

```
Macro run
Story created
Before actions
Action run
```

See Laravel's [Macroable](https://laravel.com/api/9.x/Illuminate/Support/Traits/Macroable.html) documentation for more information.