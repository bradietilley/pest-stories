[Documentation](/docs/documentation.md) > Actions

- [Name](/docs/actions/name.md)
- [Generators](/docs/actions/generators.md)
- [Variables](/docs/actions/variables.md)
- [Order](/docs/actions/order.md)
- [Test Name](/docs/actions/test-name.md)

### Actions (Tasks and Scenarios)

A task (`BradieTilley\StoryBoard\Story\Task`) and scenario (`BradieTilley\StoryBoard\Story\Scenario`) are both actions (`BradieTilley\StoryBoard\Story\AbstractAction`).

Each story must have at least one task *(subject to change in future releases)* and may have multiple tasks or scenarios.

**Tasks**

Tasks are designed to return a single result, which occurs after all scenarios are booted. Example of what a task may be:
- "fetch me api endpoint" which returns the result of the /me API endpoint

**Scenarios**

Scenarios are designed to bootstrap and seed an environment based on (often-reusable) scenarios. Examples of scenarios may be:

- "as admin" which would create/fetch an admin and act as them,
- "without email verified" which would fetch the authorised user and unset the email verification timestamp simulating a pending email verification,
- "without permission" which would accept permission(s) that the authorised user should get stripped of.

Scenarios are almost synonymous with Tasks, except Scenarios have a `variable` property which allows for you to return a value that is then accessible later in the Story object (see [Data / Variables](/docs/stories/data-variables.md)).

**Workflow**

Actions are registered when a Story is registered, and booted when a Story is booted, and is always: Scenarios first, Tasks last, and both abide by their defined orders.

See [Workflow](/docs/stories/workflow.md) for more information on exactly when actions are booted.