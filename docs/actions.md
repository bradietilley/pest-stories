[Documentation](/docs/documentation.md) > Actions

- [Name](/docs/actions/name.md)
- [Generators](/docs/actions/generators.md)
- [Variables](/docs/actions/variables.md)
- [Order](/docs/actions/order.md)
- [Test Name](/docs/actions/test-name.md)

### Actions

An action (`BradieTilley\StoryBoard\Story\Action`) is designed to bootstrap and seed an environment based on (often-reusable) scenarios. Each story may have multiple actions, but requires at least one.

Examples of actions may be:

- "as admin"
    - this would create/fetch an admin and act as them
- "without email verified"
    - this would fetch the authorised user and unset the email verification timestamp simulating a pending email verification
- "without permission"
    - this action would allow arguments (permission(s)) that the authorised user should get stripped of

**Similarities**

Actions are almost synonymous with Tasks, except Actions have a `variable` property which allows for you to return a value that is then accessible later in the Story object (see [Data / Variables](/docs/stories/data-variables.md) for how you may access these variables elsewhere). Tasks do not have a variable, instead the returned value is accessible in the assertion checker as the variable `$result`.

**Workflow**

Actions are registered when a Story is registered, and booted when a Story is booted, and is always: Actions first, Tasks last, and both abide by their defined orders.

See [Workflow](/docs/stories/workflow.md) for more information on exactly when actions are booted.