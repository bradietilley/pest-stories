# Pest StoryBoard

Provides an object-oriented approach for writing large test suites, with the ability to leverage shared scenarios, assertions and tasks.

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
Scenario::make('as_admin')
    ->as(fn (Story $story) => $story->user(createUserWithRole('admin')))
    ->appendName('as admin');

Scenario::make('as_publisher')
    ->as(fn (Story $story) => $story->user(createUserWithRole('publisher')))
    ->appendName('as publisher');

Scenario::make('as_customer')
    ->as(fn (Story $story) => $story->user(createUserWithRole('customer')))
    ->appendName('as customer');

Scenario::make('as_blocked')
    ->as(fn (Story $story, User $user) => $story->user()->block())
    ->variable('blocked')
    ->appendName('when blocked');

Scenario::make('not_blocked')
    ->as(fn (Story $story, User $user) => null)
    ->variable('blocked')
    ->appendName('when not blocked');

StoryBoard::make()
    ->name('create a post')
    ->before(
        fn () => assertDatabaseMissing('posts', [
            'content' => 'Some Content',
        ]),
    )
    ->task(function (Story $story, TestCase $test) {
        $test->post(route('posts.store'), [
            'content' => 'Some Content',
        ]);
    })
    ->check(
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
        Story::make()->scenario('as_blocked')->stories([
            Story::make()->scenario('as_admin'),
            Story::make()->scenario('as_publisher'),
            Story::make()->scenario('as_customer'),
        ])->cannot(),

        Story::make()->scenario('not_blocked')->stories([
            Story::make()->scenario('as_admin'),
            Story::make()->scenario('as_publisher'),
            Story::make()->scenario('as_customer'),
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

Often you'll need to test the same feature of an application with many different scenarios (roles, ownership, permissions, etc) especially when it comes to policy-based or validation-based tests.

If you have 5 or more roles where each role has varying permissions, and some permissions are dependent on scope-based or ownership-based access, then you'll find yourself with a massive test case that has a lot of dataset parameters and `if this foo then do bar and if biz then do baz`. After months when scopes change, new permissions are added, and new access rules are implemented, you'll find yourself staring at your old code wondering if it needs to be refactored, as maybe there's certain scenarios you should test to ensure the thousands of possible actions are covered. Last thing you want is access elevation because you added a new feature and didn't want to consider the hundred new permutations of scenarios that would be a nightmare to implement in the ways of traditional test cases.  

With Pest StoryBoard, the idea is to keep the code clean and to follow the DRY principle by keeping everything reusable where it logically makes sense. Your stories will be clearer and easier to read, which allows you to paint the picture of what you're testing much more effectively, in a way that is also easier to maintain.

```
StoryBoard (Scenario 1)
|
+---------- Story 1 (Scenario 2)
|           | 
|           +---------- Story 1A (Scenario 4)
|           |           |
|           |           +---------- Story 1A1 (Scenario 6)
|           |           |
|           |           +---------- Story 1A2 (Scenario 7)
|           |
|           +---------- Story 1B (Scenario 5)
|                       |
|                       +---------- Story 1B1 (Scenario 6)
|                       |
|                       +---------- Story 1B2 (Scenario 7)
|
+---------- Story 2 (Scenario 3)
            |
            +---------- Story 2A (Scenario 4)
            |           |
            |           +---------- Story 2A1 (Scenario 6)
            |           |
            |           +---------- Story 2A2 (Scenario 7)
            |
            +---------- Story 2B (Scenario 5)
                        |
                        +---------- Story 2B1 (Scenario 6)
                        |
                        +---------- Story 2B2 (Scenario 7)
```

Not convinced? Read over the documentation to see the full potential of this library to understand why you might want to use building blocks as opposed to writing each test from scratch.
