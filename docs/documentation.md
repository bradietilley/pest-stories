# Pest StoryBoard Documentation

- [Stories](/docs/stories.md)
- [Actions: Actions](/docs/actions.md)

## Stories

A story (`BradieTilley\StoryBoard\Story`) is a full test (from start to finish) for your application's features or limitations.

A story may be comprised of:

- one or more actions (see `Actions` below)
- an expectation of you _"can"_ or _"cannot"_ perform the action, where applicable
- an assertion where you check if the actual results are as you expect

You play with building blocks to form a situation where a feature/limitation test stems from, then you perform an action and assert the results are as expected. Simple! 

Read more about [Stories](/docs/stories.md).

## Actions

An action (`BradieTilley\StoryBoard\Stories\AbstractAction`) is a closure-driven callback that may be used in one or more stories, in order to execute a sequence of events/actions that lead to a state where you can test a given feature/limitation.

A Action (`BradieTilley\StoryBoard\Stories\Action`) is designed to seed an environment either before you begin testing, such as creating existing records, modifying configuration values, etc, and are also designed to perform actions where may assert your expectations against.

Read more about [Actions](/docs/actions.md).
