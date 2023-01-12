# Pest StoryBoard

Provides an object-oriented approach for writing large test suites, with the ability to leverage shared actions, assertions and actions.

This of this 

### Installation

Composer, how else?

```
composer require bradietilley/pest-storyboard
```

### Documentation

Read the available documentation [here](/docs/documentation.md).


### Usage

Example of 'basic' usage:

```php
Action::make('as_admin')
    ->as(fn (Story $story) => $story->user(createUserWithRole('admin')))
    ->appendName('as admin');

Action::make('as_publisher')
    ->as(fn (Story $story) => $story->user(createUserWithRole('publisher')))
    ->appendName('as publisher');

Action::make('as_customer')
    ->as(fn (Story $story) => $story->user(createUserWithRole('customer')))
    ->appendName('as customer');

Action::make('as_blocked')
    ->as(fn (Story $story, User $user) => $story->user()->block())
    ->variable('blocked')
    ->appendName('when blocked');

Action::make('not_blocked')
    ->as(fn (Story $story, User $user) => null)
    ->variable('blocked')
    ->appendName('when not blocked');

Story::make()
    ->name('create a post')
    ->before(
        fn () => assertDatabaseMissing('posts', [
            'content' => 'Some Content',
        ]),
    )
    ->action(function (Story $story, TestCase $test) {
        $test->post(route('posts.store'), [
            'content' => 'Some Content',
        ]);
    })
    ->assert(
        can: function (TestCase $test) {
            $test->assertCreated();

            assertDatabaseHas('posts', [
                'content' => 'Some Content',
            ]);
        },
        cannot: function (TestCase $test) {
            $test->assertForbidden();

            assertDatabaseMissing('posts', [
                'content' => 'Some Content',
            ]);
        },
    )->stories([
        Story::make()->action('as_blocked')->stories([
            Story::make()->action('as_admin'),
            Story::make()->action('as_publisher'),
            Story::make()->action('as_customer'),
        ])->cannot(),

        Story::make()->action('not_blocked')->stories([
            Story::make()->action('as_admin'),
            Story::make()->action('as_publisher'),
            Story::make()->action('as_customer'),
        ])->can(),
    ])
    ->test();
```

The following tests are executed:

- [Can] create a post when not blocked as an admin
    - Creates an admin and acts as them
    - Assert 'Some Content' post does not exist
    - POST to /posts
    - Assert 201 Created
    - Assert 'Some Content' post exists
- [Can] create a post when not blocked as a publisher
    - Creates a publisher and acts as them
    - Assert 'Some Content' post does not exist
    - POST to /posts
    - Assert 201 Created
    - Assert 'Some Content' post exists
- [Can] create a post when not blocked as a customer
    - Creates a customer and acts as them
    - Assert 'Some Content' post does not exist
    - POST to /posts
    - Assert 201 Created
    - Assert 'Some Content' post exists
- [Cannot] create a post when blocked as an admin
    - Creates an admin and acts as them
    - Gets authorised user (admin) and blocks them
    - Assert 'Some Content' post does not exist
    - POST to /posts
    - Assert 403 Forbidden
    - Assert 'Some Content' post does not exist
- [Cannot] create a post when blocked as a publisher
    - Creates a publisher and acts as them
    - Gets authorised user (publisher) and blocks them
    - Assert 'Some Content' post does not exist
    - POST to /posts
    - Assert 403 Forbidden
    - Assert 'Some Content' post does not exist
- [Cannot] create a post when blocked as a customer
    - Creates a customer and acts as them
    - Gets authorised user (customer) and blocks them
    - Assert 'Some Content' post does not exist
    - POST to /posts
    - Assert 403 Forbidden
    - Assert 'Some Content' post does not exist

### Why?

Often you'll need to test the same feature of an application with many different actions (roles, ownership, permissions, etc) especially when it comes to policy-based or validation-based tests.

If you have 5 or more roles where each role has varying permissions, and some permissions are dependent on scope-based or ownership-based access, then you'll find yourself with a massive test case that has a lot of dataset parameters and `if this foo then do bar and if biz then do baz`. After months when scopes change, new permissions are added, and new access rules are implemented, you'll find yourself staring at your old code wondering if it needs to be refactored, as maybe there's certain actions you should test to ensure the thousands of possible actions are covered. Last thing you want is access elevation because you added a new feature and didn't want to consider the hundred new permutations of actions that would be a nightmare to implement in the ways of traditional test cases.  

With Pest StoryBoard, the idea is to keep the code clean and to follow the DRY principle by keeping everything reusable where it logically makes sense. Your stories will be clearer and easier to read, which allows you to paint the picture of what you're testing much more effectively, in a way that is also easier to maintain.

```
StoryBoard (Action 1)
|
+---------- Story 1 (Action 2)
|           | 
|           +---------- Story 1A (Action 4)
|           |           |
|           |           +---------- Story 1A1 (Action 6)
|           |           |
|           |           +---------- Story 1A2 (Action 7)
|           |
|           +---------- Story 1B (Action 5)
|                       |
|                       +---------- Story 1B1 (Action 6)
|                       |
|                       +---------- Story 1B2 (Action 7)
|
+---------- Story 2 (Action 3)
            |
            +---------- Story 2A (Action 4)
            |           |
            |           +---------- Story 2A1 (Action 6)
            |           |
            |           +---------- Story 2A2 (Action 7)
            |
            +---------- Story 2B (Action 5)
                        |
                        +---------- Story 2B1 (Action 6)
                        |
                        +---------- Story 2B2 (Action 7)
```

Not convinced? Read over the documentation to see the full potential of this library to understand why you might want to use building blocks as opposed to writing each test from scratch.
