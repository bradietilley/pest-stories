[Documentation](/docs/documentation.md) > [Stories](/docs/stories.md) > Test Case

### Story Test Case

During the creation/writing of a Story, it is not bound by a `TestCase`. Only when a story is tested, will you have access to the `TestCase`.

Example:

```php
$story = Story::make()->action(function (TestCase $test) {
    // Have access in actions, assertions (checks)
});

// Don't have access before testing
$story->getTest(); // null
```

See [Workflow](/docs/stories/workflow.md) for more information.