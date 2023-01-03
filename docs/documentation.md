# Pest StoryBoard Documentation

- [Stories](/docs/stories.md)
- [Actions: Scenarios + Tasks](/docs/actions.md)

## Stories

A `BradieTilley\StoryBoard\Story` is a from-start-to-finish "story" about your application's features, access restrictions, etc. Similarly, a `BradieTilley\StoryBoard\StoryBoard` is 99% the same as a story (per current release) but with better dataset handling (when you enable datasets).

A story is built up of zero-or-more scenarios and one-or-more tasks (see below), an expectation of you 'can' or 'cannot' perform this action, and an assertion that asserts that the actual results were as expected.

You play with building blocks and build a scenario of which a feature or restriction you're testing stems from, then you perform an task of which you then assert is what you want. Simple! 

Read more about [Stories](/docs/stories.md).

## Actions

A `BradieTilley\StoryBoard\Stories\AbstractAction` is a closure driven callback that may be used in one or more stories in order to execute a sequence of events/actions that lead to a state where you can test a given feature, access restriction or whatever.

There are two different types of actions - a Scenario and a Task.

Scenarios are designed to seed an environment before you begin testing, such as creating existing records, modifying configuration values, etc.

Tasks are designed to perform actions of which the result may be asserted against. Typically you only need a single Task, however you may have multiple.

Together, the can paint the picture of what you're testing:
    - Scenario: create admin, login as admin.
    - Task: impersonate another user, perform some action whilst impersonating.

Then you test if the (last) task (some action whilst impersonating) was successful/prohibited.

Read more about [Actions](/docs/actions.md).
