[Documentation](/docs/documentation.md) > [Actions](/docs/actions.md) > Repeating

### Action Repeating

There may be situations where you wish to run an action several times, for example when you need to test access limitations (429). You can achieve this by passing `->repeat()` to an action.

Usage:

```php
// Without repeating (same as 1x repeat)
Action::make('post')->as(fn () => Post::factory()->create());

// With repeating 5x
Action::make('post_has_many_comments')
    ->as(fn (Post $post) => Comment::factory()->for($post)->create())
    ->repeat(5);

// Never run
Action::make('something_else')->as(fn () => doSomething())->repeat(0);

// Typical usage
Story::make('some test')
    ->can()
    ->action('post')
    ->action('post_has_many_comments')
    ->run();
```
