[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Workflow / Testing

### Story Workflow / Testing

A `StoryBoard` should be used as the highest level story, with `Story` for _all_ children and grandchildren, but this is not enforced as there's little-to-no difference between a StoryBoard and a Story.

You can register a `Story` or `StoryBoard` test via the `->test()` method. When the stories are created, the actions and assertions are not executed.

Example:

```php
$run = collect();

StoryBoard::make('a test')
    ->can()
    ->action(fn () => $run[] = 'now')
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
    ->action(fn () => null)
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
Story::make()->action(function (Story $story, TestCase $test) {
    $story->getTest() === $test; // true
});
```

**Story Registration + Boot**

Take the following example as a depiction of the order of events:

```php
Action::make('action')
    ->as(fn () => echo "action run")
    ->registering(fn () => echo "action register")
    ->booting(fn () => echo "action boot");

StoryBoard::make()
    ->can()
    ->before(fn () => echo "action before")
    ->action('action')
    ->after(fn () => echo "action after")
    ->assert(fn () => echo "assert run")
    ->test();
```

Output would be:

```
action before
action register
action boot
action run
action after
assert run
```
