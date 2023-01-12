[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Timeouts

### Story Timeouts

Your application may require features to operate within certain timeframes and never exceed a predefined timeout. In storyboard, you can achieve this by supplying a `->timeout()` on a story (parent or child).

By specifying a timeount, the story's process (including registering and booting) will abort after the given amount of time (see notes below). For example if you set a timeout of 2 seconds and your story/actions take 2 seconds, it will abort and will fail with an appropriate message.

```php
Story::make('parent')
    ->can()
    ->action(fn () => sleep(2)); // long running process
    ->timeout(3)
    ->stories([
        Story::make('child 1'), // will pass (takes 2 seconds to run; timeout of 3 seconds)
        Story::make('child 2')->timeout(1), // will fail (takes 2 seconds to run; timeout of 1 second)
    ]);
```

The `[Can] parent child 2` story would fail with the following message:

> Failed asserting that this action would complete in less than 2 seconds.

Notes:

You may specify any timeout down to the microseconds. If a job has a decimal timeout (e.g. `1.25` seconds) exceeds the specified timeout, it will fail, however it won't immediately abort the process until the next full second (i.e. `2` seconds).