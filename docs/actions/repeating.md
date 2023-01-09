[Documentation](/docs/documentation.md) > [Actions](/docs/actions.md) > Repeating

### Action Repeating

There may be situations where you wish to run an action (action or task) several times. You can achieve this by passing `->repeat()` to an action.

```php
Action::make('post')
    ->as(fn () => Post::factory()->create());

Action::make('post_has_many_comments')
    ->as(fn (Post $post) => Comment::factory()->for($post)->create())
    ->repeat(5);

Story::make('some test')
    ->can()
    ->task(fn () => expect(null)->toBeNull())
    ->action('post')
    ->action('post_has_many_comments')
    ->run();
```
