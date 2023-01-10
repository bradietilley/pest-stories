[Documentation](/docs/documentation.md) > Actions

- [Name](/docs/actions/name.md)
- [Generators](/docs/actions/generators.md)
- [Variables](/docs/actions/variables.md)
- [Order](/docs/actions/order.md)
- [Test Name](/docs/actions/test-name.md)

### Actions

An action (`BradieTilley\StoryBoard\Story\Action`) is designed to bootstrap and seed an environment based on (often-reusable) scenarios. Each [Story](/docs/stories.md) may have multiple actions, but requires at least one.

Examples of actions may be:

```php
Action::make('as_admin')->as(function (Story $story) {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $story->user($user);
});
```

This would create a user, assign them the admin role, and set the user of the story, and authorise/log in as them.

```php
Action::make('unverified_email')->as(function (User $user) {
    $user->update([
        'email_verified_at' => null,
    ]);
});
```

This would fetch the authorised user, update the `email_verified_at` field to be `null`, to simulate a pending email verification.

```php
Action::make('without permission')->as(function (User $user, array|string $permission) {
    $user->revokePermissions($permission);
});
```

This would require the `permission` argument when used (more on this), fetch the authorised user, and revoke the passed `permission` from the user.

**Variables**

Actions have a `variable` property which allows for you to return a value that is then accessible later in the Story object via callbacks (see [Data / Variables](/docs/stories/data-variables.md) for how you may define and later access these variables elsewhere).

**Assertions**

When performing an assertion, you have access to a `$result` variable/argument. The result is passed through each action (optionally used of course), and is set to the result from each action (and thus the most recent action's return value becomes the `$result`). Read more about [Assertions](/docs/stories/assertions.md) for more infor.

**Workflow**

Actions are registered when a Story is registered, and booted when a Story is booted. The action's callback (`->as()`) is executed in the context of the Story when booted. See [Workflow](/docs/stories/workflow.md) for more information on exactly when actions are booted.