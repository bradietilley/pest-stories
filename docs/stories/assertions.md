[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Assertions

### Story Assertions

All your Pest `expect` and phpunit `assert` calls should be done in the story's assertion `check()` method.

The `check()` method accepts two callbacks, one for each [Expectation](/docs/stories/expectations.md), i.e. `can` and `cannot`. Of course there will be scenarios were you're testing things that don't fit this model of can/cannot, and as such you'll find yourself just using the `can` assertion check and have it contain your complex assertions.

Rudimentary Example:

```php
Story::make('write a post')
    ->check(
        can: fn () => expect(Post::count())->toBe(1)
        cannot: fn () => expect(Post::count())->toBe(0)
    )
    ->task('create_post_api_request')
    ->stories([
        Story::make()->scenario('as_admin')->can(),
        Story::make()->scenario('as_user')->cannot(),
    ]);
```