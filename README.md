# Pest Stories

An object-oriented approach for writing large test suites.

![Static Analysis](https://github.com/bradietilley/pest-stories/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/pest-stories/actions/workflows/tests.yml/badge.svg)

### Installation

```
composer require bradietilley/pest-stories --dev
```

### Introduction

User stories are short, simple descriptions of a feature or functionality of a software application, typically written from the perspective of an end user.

Pest Stories is a PHP package that extends the PestPHP testing framework, allowing developers to write user stories in a clear and reusable way, making it easier to maintain and test their software applications.

### Documentation

Read the [documentation](/docs/README.md).

TL;DR: Here's a quick run down:

First, let's build a simple example including a few global actions and assertions, and a single
story tree.

```php
// e.g. tests/Pest.php or another autoloaded Pest file.

/**
 * Here we'll create an action that we could use for any test. It represents a fragment
 * of the system which is the API Create Product endpoint. With the payload argument we
 * can modify how this behaves: it could succeed or it could fail validation, depending
 * on the payload provided at the time of utilising the action.
 * 
 * The result of the callback (instance of TestResponse) will be assigned to the story
 * variable 'response' instead of the default 'api_create_product' variable as it was
 * overridden.
 */
action('api_create_product')
    ->as(
        fn (TestCase $test, array $payload) => $test->post('/products', array_replace([
            'title' => 'Something',
            'slug' => 'something',
        ], $payload))
    )
    ->for('response');

/**
 * Throughout the API, you'll no doubt be testing status codes, and perhaps you'll even
 * be expecting a standardised JSON response structure like:
 *      { status: int, success: bool, message: string, data: object }
 * 
 * By creating standardised assertions for each status code, you'll be able to roll out
 * changes with changes only made to a single assertion.
 */
assertion('true')->as(fn (TestResponse $response) => $response->assertStatus(200)->assertJson([ ... ]));
assertion('201')->as(fn (TestResponse $response) => $response->assertStatus(201));
assertion('422')->as(
    fn (TestResponse $response, array $errors = []) => $response->assertStatus(422)->assertInvalid($errors)
);

// e.g. tests/Feature/Api/Products/CreateTest.php

/**
 * For this story we'll test various attempts at creating a product via the API. By default,
 * the 'api_create_product' action has a 'good payload' and will accept modifications to be
 * merged in via the 'payload' variable.
 */
story('Create Product endpoint')
    /**
     * Before the story itself, or any actions/assertions run, assert that we are in fact
     * missing a product called Something.
     */
    ->before(fn () => assertDatabaseMissing('products', [
        'title' => 'Something',
    ]))

    /**
     * Register the 'api_create_product' action to run on this story and/or any descendants
     */
    ->action('api_create_product')
    
    /**
     * Make this story a Parent Story. By doing so, the parent story will not run by itself
     * and instead merely acts as a source of inheritance or callback sharing.
     */
    ->stories([
        /**
         * A child story with no children = will test.
         * 
         * This story will be called "Create Product endpoint with good payload", and will run
         * the 'before' callback from above and 'api_create_product' and will then continue:
         * 
         * The 'response' variable from the 'api_create_product' action is then given to the
         * '201' assertion, which asserts the status code as well as expected JSON structure.
         * 
         * If that passes, it'll continue with an inline assertion that asserts a product now
         * exists with the title set to 'Something' and slug set to 'something' 
         */
        story('with good payload')
            ->assertion('201')
            ->assertion(fn (array $payload) => assertDatabaseExists('products', [
                'title' => 'Something',
                'slug' => 'something',
            ])),
        /**
         * The next story to run is that passing a 'null' for the slug is accepted, and in fact
         * means the server defaults it to the kebab-case of the provided title, i.e. 'something' 
         * 
         * We'll set the payload to be an array with a null slug value. The payload variable
         * is now [ 'slug' => null ]
         * 
         * In the 'api_create_product' we fetch the payload variable and array_replace the
         * default which includes a title of 'Something' and slug of 'something'. This will
         * eventually result in [ 'title' => 'Something', 'slug' => null ]
         * 
         * We still expect the product to be created (201) and we expect the product to exist in
         * the database, with the slug default to 'something'
         */
        story('with missing slug defaulted to kebab case of title')
            ->with([
                'payload' => [
                    'slug' => null,
                ],
            ])
            ->assertion('201')
            ->assertion(fn () => assertDatabaseExists('products', [
                'title' => 'Something',
                'slug' => 'something',
            ])),
        /**
         * The next story to run is one that asserts the title field is a required field.
         * 
         * We'll set the payload to be the opposite to above: missing a title, but slug is still
         * 'something' as per the default payload in 'api_create_product' action.
         * 
         * Unlike the above, we expect this to fail validation, which we've configured as the
         * '422' assertion. This assertion accepts an 'errors' argument which in this case we'll
         * set as the expected title field validation error.
         */
        story('fails if missing title')
            ->with([
                'payload' => [
                    'title' => null,
                ],
            ])
            ->assertion('422', [
                'errors' => [
                    'title' => 'The title field is required.',
                ],
            ]),
        // fails if missing X
        // fails if missing Y
        // fails if missing Z
    ])
    /**
     * Now that the stories have been defined, we register the story with Pest. This is similar
     * to running test('...', fn () => ...) against each child story.
     */
    ->test();

/**
 * Test cases:
 * 
 * Create Product endpoint with dataset "with good payload"
 * Create Product endpoint with dataset "with missing slug defaulted to kebab case of title"
 * Create Product endpoint with dataset "fails if missing title"
 */
```

### Overivew

Under the hood, Pest Stories is largely comprised of a few `Callback` classes: `Action`, `Assertion` and `Story`.

Each `Callback` class represents a fragment of your application, that is often replicable and common (not always).

For `Action` callbacks, these could be small scenarios like:

- creating an administrator
- creating an unpaid invoice for a customer
- testing an API endpoint

For `Assertion` callbacks, these could be small expectations like:

- expecting a policy passes
- expecting a record exists in the database
- expecting the previous API response to have a status code of `201`

For `Story` callbacks, these form larger "stories" including many actions and assertions:

- as an administrator (action), create a resource via the api (action), expect 201 (assertion)
- etc

Each `Callback` represents a `Closure` callback with a few extra helpful features:

- Primary callback:
    - This is the `Closure` that the `Callback` represents.
- `before` callback:
    - This allows you to fire a custom callback (or multiple) before the primary `Closure` is run.
- `after` callback:
    - This allows you to fire a custom callback (or multiple) after the primary `Closure` is run.
- Data repository:
    - This allows you to define variables that are available to read and write throughout the lifecycle
    of the Callback, including before, primary and after callbacks.
- Dependency Injection:
    - Utilising Laravel's container, you can expect arguments in your Closure which are fed from both Laravel's container and the Data repository.

A `Story` callback allows for nested sub-stories, which is what allows you to build complex test suites with ease. If you need to test all permutations of a feature, nested stories will make this cleaner and easier to achieve.

An example of where nested stories would come in handy is testing various permutations for Policy tests. For example, let's say you need to test each role for the authorised user, cross with each role of the end user, and to make it more complex: whether the authorised + end user belong to the same parent (Location, Organisation, Tenant, etc).

To effectively test this feature, you'd want to test each role (let's say 8) against each role (another 8) under each true/false state of "same location", which is 8*8*2 = 128 permutations. 

This could be achieved in a (fairly) clean way by Pest Stories (example doesn't include location permutation though):

```php
/**
 * You might find yourself constantly referencing a given user or an "other"
 * user so you might configure a macro to help streamline the definition of
 * "in this test set the authorised user to someone with X role" like below:
 */
Story::macro('setUser', function (Role|User $user) {
    $user = ($user instanceof Role) ? User::factory()->create()->assignRole($user) : $user;

    $this->set('user', $user);
    
    return $this;
});

Story::macro('setOther', function (Role|User $user) {
    $user = ($user instanceof Role) ? User::factory()->create()->assignRole($user) : $user;

    $this->set('other', $user);
    
    return $this;
});

/**
 * Assertions could be done as inline callbacks, but if it gets too repetitive
 * you can always define common expectations as assertion callbacks and then
 * reference them by their name in stories, i.e. 'true' and 'false' as per
 * below: 
 */
assertion('false')->as(fn (bool $result) => expect($result)->toBeFalse());
assertion('true')->as(fn (bool $result) => expect($result)->toBeTrue());

/**
 * Test all roles ability to update another user of all roles, to ensure every scenario is covered.
 */
story('User Update policy')
    ->as(fn (User $user, User $other) => UserPolicy::make()->update($user, $other))
    /**
     * The response from `as()` is made available in subsequent callbacks, including
     * assertions, as the `$result` argument, as seen in the defined `true` and
     * `false` assertions above.
     */
    ->stories([
        story()->setUser(Role::ROLE_DEV)->stories([
            story()->setOther(Role::ROLE_DEV)->assert('false'),
            story()->setOther(Role::ROLE_SUPER_ADMIN)->assert('true'),
            story()->setOther(Role::ROLE_MANAGEMENT)->assert('true'),
            story()->setOther(Role::ROLE_ADMIN)->assert('true'),
            story()->setOther(Role::ROLE_VIP_USER)->assert('true'),
            story()->setOther(Role::ROLE_CUSTOMER)->assert('true'),
            story()->setOther(Role::ROLE_TRIAL)->assert('true'),
            story()->setOther(Role::ROLE_GUEST)->assert('true'),
        ]),
        story()->setUser(Role::ROLE_SUPER_ADMIN)->stories([
            story()->setOther(Role::ROLE_DEV)->assert('false'),
            story()->setOther(Role::ROLE_SUPER_ADMIN)->assert('false'),
            story()->setOther(Role::ROLE_MANAGEMENT)->assert('true'),
            story()->setOther(Role::ROLE_ADMIN)->assert('true'),
            story()->setOther(Role::ROLE_VIP_USER)->assert('true'),
            story()->setOther(Role::ROLE_CUSTOMER)->assert('true'),
            story()->setOther(Role::ROLE_TRIAL)->assert('true'),
            story()->setOther(Role::ROLE_GUEST)->assert('true'),
        ]),
        // ...
        story()->setUser(Role::ROLE_GUEST)->assert('false')->stories([
            story()->setOther(Role::ROLE_DEV),
            story()->setOther(Role::ROLE_SUPER_ADMIN),
            story()->setOther(Role::ROLE_MANAGEMENT),
            story()->setOther(Role::ROLE_ADMIN),
            story()->setOther(Role::ROLE_VIP_USER),
            story()->setOther(Role::ROLE_CUSTOMER),
            story()->setOther(Role::ROLE_TRIAL),
            story()->setOther(Role::ROLE_GUEST),
        ]),
    ]);
```

#### Workflow

All `Callback` objects (`Action`, `Assertion`, `Story`) may have any number of `before` and `after` callbacks. \
All `Story` objects may have any number of `setUp` and `tearDown` callbacks. \
All `Story` objects may have any number of `Action` and `Assertion` objects.

For `Story` objects, inheritance plays a part in the sequence of events:

All callbacks including callback closures (`before`, `after`, `setUp`, `tearDown`) and Callback objects (`Action`, `Assertion`) are
inherited from a parent and queued before the child's callbacks. This allows your parent to define the first key steps in setting up
the scenario, with the children stories adjusting the scenario further to meet the need of the story.

After inheriting all callbacks from parents, grandparents, etc, the lifecycle of a Story is as such:

- `Story` callbacks: `setUp`
- `Story` Actions (for each):
    - `Action` callbacks: `before`
    - `Action` primary callback
    - `Action` callbacks: `after`
- `Story` itself:
    - `Story` callbacks: `before`
    - `Story` primary callback
    - `Story` callbacks: `after`
- `Story` Assertions (for each):
    - `Assertion` callbacks: `before`
    - `Assertion` primary callback
    - `Assertion` callbacks: `after`
- `Story` callbacks: `tearDown`



A higher level view of the lifecycle is:

**Story registration**

This is done via the `test()` method. For example: `story()->customise(...)->test()`.

If stories are nested, it will iterate all stories until it finds the child-most story for each "branch" of the story, and then for each story it will inherit all callbacks and options from the parents.

Once all stories have been "registered" (or if there aren't any nested stories), the package then calls Pest's `test()` function with the Closure callback argumnet being the Story's internal process function which ultimately boots everything for the story.

At this stage though, no callbacks are run. All other stories are compiled and registered to form a full list of test cases to run (groups/filters are then applied by pest/phpunit) and then Pest begins the processing stage.


**Story processing**

Because the test function is native Pest functionality, you can continue to leverage the helper callbacks
like `beforeEach()` and `beforeAll`.

Pest runs the `test()` Closure callback argment, which runs the Story process: `$story->process()`.

This `process()` method will run each child story from start to finish, including all callbacks, Actions and Assertions.

Because all of this is run within a Pest `test()` function, standard features _like_ "No assertions were made" errors are still
thrown.


**Story Expectations**

Sometimes a story assertion may be overkill and you just want to run expectations against a story variable.

Instead of doing this:

```php
action('create_product')->as(fn (array $data) => Product::factory()->create($data))->for('product');

story()
    ->action('create_product', [
        'data' => [
            'sku' => 'ABC',
        ],
    ])
    ->action(fn () => 'DEF', for: 'something')
    ->assertion(function ($product, $something) {
        expect($product)->toBeInstanceOf(Product::class)
            ->wasRecentlyCreated->toBeTrue()
            ->sku->toBe('ABC')
            ->and($something)->toBe('DEF');
    });
```

You could instead do:

```php
action('create_product')->as(fn (array $data) => Product::factory()->create($data))->for('product');

story()
    ->action('create_product', [
        'data' => [
            'sku' => 'ABC',
        ],
    ])
    ->action(fn () => 'DEF', for: 'something')
    ->expect('product') // or ->expect(fn (Story $story) => $story->get('product'))
    ->toBeInstanceOf(Product::class)
    ->wasRecentlyCreated->toBeTrue()
    ->sku->toBe('ABC');
    ->and('something')
    ->toBe('DEF')
```

