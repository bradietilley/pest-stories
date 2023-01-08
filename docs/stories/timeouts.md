[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Timeouts

### Story Timeouts

You may specify a timeout for a story. Doing so will terminate the story's process after the given time (rounded up to the nearest second). If the timeout is exceeded during the task's process (including registering and booting) then the story will fail with an appropriate message.

```php
StoryBoard::make('parent')
    ->can()
    ->task(fn () => sleep(2)); // long running process
    ->timeout(3)
    ->stories([
        Story::make('child 1'), // will pass (takes 2 seconds; max of 3 seconds)
        Story::make('child 2')->timeout(1), // will fail (takes 2 seconds; max of 1 second)
    ]);
```

The `[Can] parent child 2` story would fail with the following message:

> Failed asserting that this task would complete in less than 2 seconds.

