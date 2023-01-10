[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Test Case

### Story Test Case

During the creation/writing of a Story, it is not bound by a `TestCase`. Only when a story is tested, will you have access to the `TestCase`. This is in line with how Pest works -- when you write your `test('name', fn () => ...)` you don't have access to a `TestCase` instance; it's not until the function (second) argument is executed that you have access to the `TestCase`.

Example:

```php
$story = Story::make()->action(function (Story $story, TestCase $test) {
    // Have access in actions, assertions (checks)

    $story->getTest(); // P\Tests\Unit\YourTest
    $test; // P\Tests\Unit\YourTest
});

// Don't have access before testing
$story->getTest(); // null
```

See [Workflow](/docs/stories/workflow.md) for more information.