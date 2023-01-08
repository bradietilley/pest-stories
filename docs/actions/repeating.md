[Documentation](/docs/documentation.md) > [Actions](/docs/actions.md) > Repeating

### Action Repeating

There may be situations where you wish to run an action (scenario or task) several times. You can achieve this by passing `->repeat()` to an action.

```php
Scenario::make('post')
    ->as(fn () => Post::factory()->create());

Scenario::make('post_has_many_comments')
    ->as(fn (Post $post) => Comment::factory()->for($post)->create())
    ->repeat(5);

Story::make('some test')
    ->can()
    ->task(fn () => expect(null)->toBeNull())
    ->scenario('post')
    ->scenario('post_has_many_comments')
    ->run();
```
