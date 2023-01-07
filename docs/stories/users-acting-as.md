[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Users

### Story Users

By default when a story is run the session is unauthenticated. You may authenticate a user via the `user()` or `setUser` methods for a story. Authentication is immediately performed when `user()` is run, so this is best done via scenarios or tasks which are run when the story is booted, not when the story is written. See [Workflow](/docs/stories/workflow-testing.md) for more information.

Example:

```php
Scenario::make('as_admin', function (Story $story) {
    $story->user(User::factory()->create([
        'is_admin' => true,
    ]));
});

Task::make('logout', fn (Story $story) => $story->user(null));
```

Note: Your `User` model must have the `Illuminate\Contracts\Auth\Authenticatable` interface. If your `User` model extends a custom base model (instead of Laravel's Authenticatable), be sure to add `Illuminate\Contracts\Auth\Authenticatable` interface to your model.

**Authentication under the hood**

By default, the `->user()` method is synonymous with `auth()->login()` when `Illuminate\Contracts\Auth\Authenticatable` is provided, or `auth()->logout()` when `null` is provided.

If you have a custom authentication system, or deal with multiple authentication systems (e.g. session for one API, passport for another) then you may replace the authentication system used by StoryBoard. This can be done via the `actingAs()` static method.

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

Scenario::make('as_admin')->as(fn (Story $story) => $story->user(createAdmin()));
Scenario::make('session')->variable('auth')->as(fn () => 'session')->appendName('via session');
Scenario::make('passport')->variable('auth')->as(fn () => 'passport')->appendName('via passport');

Task::make('logout')->as(fn (TestCase $test) => $test->post('/logout'));

Story::make('logout successfully')
    ->can()
    ->scenario('as_admin')
    ->task('logout')
    ->before(
        fn () => expect(auth()->check())->toBeTrue(),
    )
    ->check(
        fn () => expect(auth()->check())->toBeFalse(),
    )
    ->stories([
        Story::make()->scenario('session'),
        Story::make()->scenario('passport'),
    ]);

/**
 * [Can] logout successfully via session
 * [Can] logout successfully via passport
 * 
 * Both assert auth()->check() is true before 'logout' task is run
 * Both assert auth()->check() is false after the 'logout' task is run
 */
```

See [Data / Variables](/docs/stories/data-variables.md) for more information on how the 'auth' variable was accessible in this example.