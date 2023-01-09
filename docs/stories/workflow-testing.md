[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Workflow / Testing

### Story Workflow / Testing

A `StoryBoard` should be used as the highest level story, with `Story` for _all_ children and grandchildren, but this is not enforced as there's little-to-no difference between a StoryBoard and a Story.

You can register a `Story` or `StoryBoard` test via the `->test()` method. When the stories are created, the actions, tasks and assertions are not executed.

Example:

```php
$run = collect();

StoryBoard::make('a test')
    ->can()
    ->task(fn () => $run[] = 'now')
    ->test();

$run->count(); // 0 
```

After Pest discovers all tests, it will then execute all Story tests.

#### The order of events

**Story test is created (Pest discovery of tests)**

By running `->test()`, the `StoryBoard` or `Story` is made available to Pest. 

```php
StoryBoard::make('create something')
    ->can()
    ->task(fn () => null)
    ->stories([
        Story::make('with a')->action('a'),
        Story::make('with b')->action('b'),
        Story::make('with c')->action('c'),
    ])
    ->test();

/**
 * Pest has compiled a list of tests to run:
 * 
 * [Can] create something with a
 * [Can] create something with b
 * [Can] create something with c
```

**Story test is run (Pest execution)**

From this point onwards, each Story has its test case available and is accessible via `$story->getTest()` or via any closure driven callback, for example:

```php
Story::make()->task(function (Story $story, TestCase $test) {
    $story->getTest() === $test; // true
});
```

**Story Registration + Boot**

1: The story is registered, which involves action and task registration.

2: Each action invokes its optional `->registering()` callback, if specified.

3: Each task invokes its optional `->registering()` callback, if specified.

4: The story is then booted, which involves action and task booting.

5: Each action invokes its optional `->booting()` callback, if specified, then immediately invokes its [Generator](/docs/actions.md#generators). 

6: Before tasks are booted, the story invokes its optional `->before()` callback, if specified.

7: Each task invokes its optional `->booting()` callback, if specified.

8: Each task invokes its required [Generator](/docs/actions.md#generators).

9: After tasks are booted, the story invokes its optional `->after()` callback, if specified.

10: The story invokes its required assertion checker (based on the specified expectation of can or cannot).

Take the following example as a depiction of the order of events:

```php
Task::make('task')
    ->as(fn () => echo "task run")
    ->registering(fn () => echo "task register")
    ->booting(fn () => echo "task boot");

Action::make('action')
    ->as(fn () => echo "action run")
    ->registering(fn () => echo "action register")
    ->booting(fn () => echo "action boot");

StoryBoard::make()
    ->can()
    ->before(fn () => echo "task before")
    ->task('task')
    ->action('action')
    ->after(fn () => echo "task after")
    ->check(fn () => echo "assert run")
    ->test();
```

Output would be:

```
action register
task register
action boot
action run
task before
task boot
task run
task after
assert run
```
