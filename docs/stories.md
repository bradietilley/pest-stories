[Documentation](/docs/documentation.md) > Stories

- [Inheritance](/docs/stories/inheritance.md)
- [Name](/docs/stories/name.md)
- [Actions: Tasks + Scenarios](/docs/stories/actions.md)
- [Expectations](/docs/stories/expectations.md)
- [Assertions](/docs/stories/assertions.md)
- [Users / Acting-As](/docs/stories/users-acting-as.md)
- [Test Case](/docs/stories/test-case.md)
- [Data / Variables](/docs/stories/data-variables.md)
- [Workflow / Testing](/docs/stories/workflow-testing.md)
- [Conditions](/docs/stories/conditions.md)
- [Macros](/docs/stories/macros.md)

## Stories

In storyboard, a `Story` is a start-to-finish test case for a 'user story', including all setup and assertions. For example: "as a customer you may create a comment if the post is not locked and the user has ability to post".

In this case, you have many scenarios:

- User exists:
    - Is a customer
    - Has ability to post
        - Not blocked
        - Has enough points
- Post exists
    - Post is not locked
    - Post is not deleted

In a standard pest test, you would probably find yourself writing anonymous functions or using factories to create these models.

```php
$user = createUser(Role::CUSTOMER);
$post = Post::factory()->create();
```

Then you'd probably find yourself testing not only the "can create when these scenarios are met" but also "cannot create when one of these scenarios are not met". You would either create a test for each scenario _(violation of DRY)_ or you'd use datasets to pass in variables that control the setup (different roles, different point levels, different blocked states, different Post lock states, different Post deleted states). Something like this:

```php
test('write comment on post', function (string $role, bool $blocked, int $points, bool $locked, bool $deleted, bool $can) {
    $user = createUser($role);
    $this->actingAs($user);

    $user->fill([
        'points' => $points,
    ])

    if ($blocked) {
        $user->block();
    }

    $user->save();

    $post = Post::factory()->create();

    if ($locked) {
        $post->lock();
    }

    if ($deleted) {
        $post->delete();
    }

    $result = (new PostPolicy())->canComment($user, $post);

    if ($can) {
        expect($result)->toBe(true);
    } else {
        if ($result instanceof Response) {
            expect($result->statusCode())->toBeIn([401, 403]);
        } else {
            expect($result)->toBe(false);
        }
    }
})->with([
    'when user is admin, can comment on post' => [
        'role' => 'admin',
        'blocked' => false,
        'points' => 0,
        'locked' => false,
        'deleted' => false,
        'can' => true,
    ],
    'when user is admin, can comment on post that is locked' => [
        'role' => 'admin',
        'blocked' => false,
        'points' => 0,
        'locked' => true,
        'deleted' => false,
        'can' => true,
    ],
    'when user is admin, can comment on post that is soft deleted' => [
        'role' => 'admin',
        'blocked' => false,
        'points' => 0,
        'locked' => false,
        'deleted' => true,
        'can' => true,
    ],
    'when user is customer, can comment on post' => [
        'role' => 'customer',
        'blocked' => false,
        'points' => 100, // enough
        'locked' => false,
        'deleted' => false,
        'can' => true,
    ],
    'when user is customer, cannot comment on post if blocked' => [
        'role' => 'customer',
        'blocked' => true,
        'points' => 100,
        'locked' => false,
        'deleted' => false,
        'can' => false,
    ],
    'when user is customer, cannot comment on post if not enough points' => [
        'role' => 'customer',
        'blocked' => false,
        'points' => 99,
        'locked' => false,
        'deleted' => false,
        'can' => false,
    ],
    'when user is customer, cannot comment on post if post that is locked' => [
        'role' => 'customer',
        'blocked' => false,
        'points' => 100,
        'locked' => true,
        'deleted' => false,
        'can' => false,
    ],
    'when user is customer, cannot comment on post if post that is soft deleted' => [
        'role' => 'customer',
        'blocked' => false,
        'points' => 100,
        'locked' => false,
        'deleted' => true,
        'can' => false,
    ],
]);
```

Adding more scenarios that require testing becomes a little difficult, and can get quite messy.

The StoryBoard idea is that these scenarios are often going to be reused across the system in various tests. From policy unit tests (that require an authorised user of varying roles, and posts to exist with varying states) to API feature tests (that also require users of varying roles, and posts with varying states) to completely different tests that just require a post or user to exist in whatever state.

```php
// In tests/Pest.php you might define some global scenarios that may frequently get used.

/**
 * User scenarios
 */
Scenario::make('as_admin')
    ->as(fn (Story $story) => $story->user(createUser(Role::ADMIN)))
    ->appendName('as an admin');

Scenario::make('as_customer')
    ->as(fn (Story $story) => $story->user(createUser(Role::CUSTOMER)))
    ->appendName('as a customer');

Scenario::make('user_blocked')
    ->as(fn (User $user) => $user->block())
    ->appendName('when blocked');

Scenario::make('user_low_points')
    ->as(fn (User $user) => $user->update([
        'points' => 99
    ]))
    ->appendName('with low points');

Scenario::make('user_high_points')
    ->as(fn (User $user) => $user->update([
        'points' => 100
    ]))
    ->appendName('with high points');

/**
 * Post scenarios
 */
Scenario::make('post')->as(fn () => Post::factory()->create());
Scenario::make('post_locked')->as(fn (Post $post) => $post->lock());
Scenario::make('post_deleted')->as(fn (Post $post) => $post->delete());


// In tests/Unit/Policies/CommentTest.php
StoryBoard::make()
    ->name('write a comment on a post')
    ->check(
        can: function (bool|Response $result) {
            expect($result)->toBe(true);
        },
        cannot: function (bool|Response $result) {
            if ($result instanceof Response) {
                expect($result->statusCode())->toBeIn([401, 403]);
            } else {
                expect($result)->toBe(false);
            }
        },
    )
    ->task(function (User $user, Post $post) {
        return (new PostPolicy())->createComment($user, $post);
    })
    ->scenario('post')
    ->stories([
        Story::make()
            ->scenario('as_admin')
            ->can()
            ->stories([
                Story::make(),
                Story::make('when post is locked')->scenario('post_locked'),
                Story::make('when post is deleted')->scenario('post_deleted'),
            ]),
        Story::make()
            ->scenario('as_customer')
            ->cannot()
            ->stories([
                Story::make()->scenario('user_high_points')->can(),
                Story::make()->scenario('user_low_points'),
                Story::make()->scenario('user_blocked'),
                Story::make('when post is locked')->scenario('post_locked'),
                Story::make('when post is deleted')->scenario('post_deleted'),
            ]),
    ])
    ->test();

/**
 * Resulting Tests:
 * 
 * [Can] write a comment on a post as an admin
 * [Can] write a comment on a post as an admin when post is locked
 * [Can] write a comment on a post as an admin when post is deleted
 * [Can] write a comment on a post as a customer with high points
 * [Cannot] write a comment on a post as a customer with low points
 * [Cannot] write a comment on a post as a customer when blocked
 * [Cannot] write a comment on a post as a customer when post is locked
 * [Cannot] write a comment on a post as a customer when post is deleted
 */
```