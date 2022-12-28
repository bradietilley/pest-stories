# Pest StoryBoard

Provides an interface for writing large number of tests, using shared scenarios, assertions and tasks.

### Installation

Composer, how else?

```
composer require bradietilley/pest-storyboard
```

### Usage

```php

Scenario::make('as_role', 'role', function (Story $story, string $role) {
    $story->setUser($this->getUserByRole($role));
});

Scenario::make('as_blocked', 'blocked', function (Story $story, User $user) {
    $story->user()->block();
});

StoryBoard::make()
    ->name('create post')
    ->check(
        can: function (TestCase $test) {
            $test->assertCreated();

            assertDatabaseHas('posts', [
                'content' => 'Something',
            ]);
        },
        cannot: function (TestCase $test) {
            $test->assertForbidden();

            assertDatabaseMissing('posts', [
                'content' => 'Something',
            ]);
        },
    )->stories([
        Story::make()->name('as an admin')->scenario('as_role', [ 'role' => 'admin', ])->can(),
        Story::make()->name('as a publisher')->scenario('as_role', [ 'role' => 'publisher', ])->can(),
        Story::make()->name('as a customer')->scenario('as_role', [ 'role' => 'customer', ])
            ->stories([
                Story::make()->name('when not blocked')->can(),
                Story::make()->name('when blocked')->scenario('as_blocked')->cannot(),
            ]),
    ])
    ->test();
```

### In Depth Look

Often you'll need to test the same feature of an application with many different scenarios, especially when it comes to permission based tests, or validation based tests. You may find yourself with something like:

```php
// tests/Feature/Posts/CreateTest.php
test('create a post', function (string $role, bool $blocked = false, bool $can = false) {
    $this->actingAs($user = $this->getUserByRole($role));

    if ($blocked) {
        $user->block();
    }

    $this->post('/posts', [
        'content' => 'Something',
    ]);

    if ($can) {
        $this->assertCreated();

        assertDatabaseHas('posts', [
            'content' => 'Something',
        ]);
    } else {
        $this->assertForbidden();

        assertDatabaseMissing('posts', [
            'content' => 'Something',
        ]);
    }
})->with([
    'can create, as an admin' => [ 'admin', false, true, ]
    'can create, as a publisher' => [ 'publisher', false, true, ]
    'can create, as a customer' => [ 'customer', false, true, ]
    'cannot create, as a blocked customer' => [ 'customer', true, false, ]
]);

/**
 * create a post with "can create, as an admin"
 * create a post with "can create, as a publisher"
 * create a post with "can create, as a customer"
 * create a post with "cannot create, as a blocked customer"
 */
```

While the above is relatively clean and somewhat reusable, it can get really out of hand when you're having to test many more roles, each of which may then have multiple scenarios (e.g. 'can create on behalf of another user', or 'can access data from other users who have granted you access', etc).

With Pest StoryBoard, the idea is to keep the code clean and reusable. Not only can you register scenarios to be re-used in a single test, but you can reuse scenarios across multiple test cases.

```php
// Pest.php -- i.e. Reusable in any test

Scenario::make('as_role', 'role', function (Story $story, string $role) {
    $story->setUser($this->getUserByRole($role));
});

Scenario::make('as_blocked', 'blocked', function (Story $story, User $user) {
    $story->user()->block();
});

// tests/Feature/Posts/CreateTest.php
StoryBoard::make()
    ->name('create post')
    ->check(
        can: function (TestCase $test) {
            $test->assertCreated();

            assertDatabaseHas('posts', [
                'content' => 'Something',
            ]);
        },
        cannot: function (TestCase $test) {
            $test->assertForbidden();

            assertDatabaseMissing('posts', [
                'content' => 'Something',
            ]);
        },
    )->stories([
        Story::make()
            ->name('as an admin')
            ->scenario('as_role', [ 'role' => 'admin', ])
            ->can(),
        Story::make()
            ->name('as a publisher')
            ->scenario('as_role', [ 'role' => 'publisher', ])
            ->can(),
        Story::make()
            ->name('as a customer')
            ->scenario('as_role', [ 'role' => 'customer', ])
            ->stories([
                Story::make()
                    ->name('when not blocked')
                    ->can(),
                Story::make()
                    ->name('when blocked')
                    ->scenario('as_blocked')
                    ->cannot(),
            ]),
    ])
    ->test();

/**
 * [Can] create post as an admin
 * [Can] create post as a publisher
 * [Can] create post as a customer when not blocked
 * [Cannot] create post as a customer when blocked
 */
```

With stories, you can nest and reuse scenarios, tasks and assertion logic, as much as you wish. This allows you to paint the picture of what you're testing much more cleanly.

```
StoryBoard
|
+---------- Story 1
|           |
|           +---------- Story 1A
|           |           |
|           |           +---------- Story 1A1
|           |           |
|           |           +---------- Story 1A2
|           |
|           +---------- Story 1B
|                       |
|                       +---------- Story 1B1
|                       |
|                       +---------- Story 1B2
|
+---------- Story 2
            |
            +---------- Story 2A
            |           |
            |           +---------- Story 2A1
            |           |
            |           +---------- Story 2A2
            |
            +---------- Story 2B
                        |
                        +---------- Story 2B1
                        |
                        +---------- Story 2B2
```

### Documentation

<a id="doc-scenarios"></a>
**Scenarios:**

With storyboards you'll want to first register each reusable scenario you might use across your app (or just in the current PHP file). 

- Name: When registering a scenario, the name is required as an identifier for when you add a scenario to a story.
- Generator: Each scenario also has a generator which is run when a story uses the scenario, which can be used to set the current user, add/update shared data across the stories, update configuration, fake facades, etc.  
- Variable: The result of generator is then stored in the Story's variables container, and can be referenced via `$story->getData($key, $default = null)`, `$story->setData($key, $value)` and `$story->allData()`.
- Order: Each scenario registration can be accompanied by an `order` which defines what order to run the scenarios in.

Registering a scenario:

```php
Scenario::make(
    name: 'as_blocked',
    variable: 'user',
    generator: fn (TestCase $test) => $story->getUser()->update([ 'blocked_at' => now() ]),
    order: 4,
);
Scenario::make(
    name: 'as_publisher',
    variable: 'user',
    generator: fn (Story $story) => $story->setUser(createPublisher()),
    order: 1,
);
```

Using a scenario:

Any story can apply a scenario via:

```php
Story::make()->scenario('as_blocked')->scenario('as_publisher');

/**
 * Order executed:
 *     as_publisher (1)
 *     as_blocked   (4)
 */
```

Inheritance is supported. You may specify the `->scenario()` on any story object, including the parent or grandparent level. Each child story will inherit the scenario from the parents.

<a id="doc-tasks"></a>
**Tasks:**

You may add one or more tasks to each story, which is the bit of logic that will run in each story/test. For example a form validation test might be:

```php
->task(function (Story $story, TestCase $test) {
    $test->post('/posts', [
        'content' => 'Something',
    ]);
})
```

Tasks are run on `$story->boot()`, not when they're registered or added to a story. 

Inheritance is supported. You may specify the `->task()` on any story object, including the parent or grandparent level. Each child story will inherit the task from the parents.

<a id="doc-expectations"></a>
**Can/Cannot expectations:**

You will need to specify what clarifies as a pass, and optionally a fail. By default, Stories support `can` and `cannot` to add clear distinction based on what each story is expected to do. They can be specified as such:

```php
->stories([
    Story::make()->scenario('a')->can(),
    Story::make()->scenario('b')->cannot(), // same as ->can(false)
])
```

_In some scenarios you may wish to avoid `cannot` entirely and instead you may solely use the `can` expectation with your own custom logic that performs its own can/cannot logic._ 

Inheritance is supported. You may specify `->can()` or `->cannot()` on any story object, including the parent or grandparent level. Each child story will inherit the can/cannot from the parents.


**Can/Cannot assertions:**

Following a `task()` execution, you will need to specify what logic needs to be run in the event of `->can()` and/or `->cannot()` via the `->check()` method. For example, a form validation test might be:

```php
->check(
    can: function (TestCase $test) {
        $test->assertCreated();

        assertDatabaseExists('posts', [
            'content' => 'Something',
        ]);
    },
    cannot: function (TestCase $test) {
        $test->assertForbidden();

        assertDatabaseMissing('posts', [
            'content' => 'Something',
        ]);
    }
)
```

_In some scenarios you may wish to avoid `cannot` entirely and instead you may solely use the `can` expectation with your own custom logic that performs its own can/cannot logic._ 

Inheritance is supported. You may specify `->check()` on any story object, including the parent or grandparent level. Each child story will inherit the assertion checks from the parents.


### TODO

- Convert Tasks to class-based entities similar to scenarios, with ordering, while still supporting inline closures.
- Add inline closure support for scenarios
- Move `variable` in scenario constructor to after generator and nullify (and inherit name of scenario)
- Add Macro support on Story
- DRY for all instances of `Container::getInstance()->call()`
- Actually ensure `TestCase` is accessible in all story callbacks (tasks, scenarios, checkers, etc)