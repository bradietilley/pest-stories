[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Users

### Story Users

By default when a story is run, the session is unauthenticated. You may authenticate a user via the `->user()` or `->setUser()` methods on a Story. Authentication is immediately performed when `->user()` is run, so this is best done via actions as actions are run when the story is booted, not when the story is written. See [Workflow](/docs/stories/workflow-testing.md) for more information on why this is important.

Example:

```php
Action::make('as_admin', function (Story $story) {
    $story->user(User::factory()->create([
        'is_admin' => true,
    ]));
});

Action::make('logout', fn (Story $story) => $story->user(null));
```

Note: Your `User` model must have the `Illuminate\Contracts\Auth\Authenticatable` interface. If your `User` model extends a custom base model (instead of Laravel's Authenticatable), be sure to add `Illuminate\Contracts\Auth\Authenticatable` interface to your model.

**Authentication under the hood**

By default, the `->user()` method is synonymous with `auth()->login()` when `Illuminate\Contracts\Auth\Authenticatable` is provided, or `auth()->logout()` when `null` is provided.

However if you have a custom authentication system, or deal with multiple authentication systems (e.g. session for one API, passport for another) then you may replace the authentication system used by StoryBoard whenever suits you. This can be done via the `Story::actingAs()` method.

Example:

```php
Story::actingAs(function (Story $story, Authenticatable $user) {
    // get the 'auth' variable for the story
    $auth = $story->get(key: 'auth', default: 'session');

    if ($auth === 'session') {
        authenticateViaSession($user);
    } elseif ($auth === 'passport') {
        authenticateViaPassport($user);
    } else {
        throw new \Exception('Invalid auth driver');
    }
});

Action::make('as_admin')->as(fn (Story $story) => $story->user(createAdmin()));
Action::make('session')->variable('auth')->as(fn () => 'session')->appendName('via session');
Action::make('passport')->variable('auth')->as(fn () => 'passport')->appendName('via passport');
Action::make('logout')->as(fn (TestCase $test) => $test->post('/logout'));

Story::make('logout successfully')
    ->can()
    ->action('as_admin')
    ->action('logout')
    ->before(
        fn () => expect(auth()->check())->toBeTrue(),
    )
    ->assert(
        fn () => expect(auth()->check())->toBeFalse(),
    )
    ->stories([
        Story::make()->action('session'),
        Story::make()->action('passport'),
    ]);

/**
 * [Can] logout successfully via session
 * [Can] logout successfully via passport
 * 
 * Both assert auth()->check() is true before 'logout' action is run
 * Both assert auth()->check() is false after the 'logout' action is run
 */
```

See [Data / Variables](/docs/stories/data-variables.md) for more information on how the 'auth' variable was accessible in this example.

Note: It is recommended that you use `$story->user($user)` with your own custom `Story::actingAs()` handler, instead of implementing your own custom acting as, as you gain access to the Story's user (not authorised user) via the closure-driven callbacks. This may be changed in future so you can still fetch the user without `$story->user()`... :shrug: