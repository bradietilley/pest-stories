[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Assertions

### Story Assertions

All your Pest `expect` and phpunit `assert` calls should be done in the story's assertion `check()` method. _However, you won't be sued if you perform your expectations in your actions..._

The `assert()` method accepts two callbacks, one for each [Expectation](/docs/stories/expectations.md), i.e. `can` and `cannot`. Of course, there may be situations were you're testing things that don't fit this model of can and cannot, and as such, you'll find yourself just using the `can` assertion check (i.e. first argument only) and have it contain your complex assertions containing whatever assertions you require.

Rudimentary Example of a can and cannot asserition:

```php
Action::make('as_admin')->as(fn (Story $story) => $story->user(createAdmin()));
Action::make('as_user')->as(fn (Story $story) => $story->user(createUser()));

Action::make('create_post_api_request')->as(function (TestCase $test) {
    $test->post('/posts', [
        'content' => 'Some content',
    ]);
});

Story::make('write a post')
    ->assert(
        can: fn () => expect(Post::count())->toBe(1)
        cannot: fn () => expect(Post::count())->toBe(0)
    )
    ->action('create_post_api_request')
    ->stories([
        Story::make('as admin')->action('as_admin')->can(),
        Story::make('as user')->action('as_user')->cannot(),
    ]);

/**
 * Test cases:
 * 
 * [Can] write a post as admin
 * [Cannot] write a post as user
 */
```