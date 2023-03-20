# Pest Stories

Provides an object-oriented approach for writing large test suites, with the ability to leverage shared actions and assertions.

![Static Analysis](https://github.com/bradietilley/pest-stories/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/pest-stories/actions/workflows/tests.yml/badge.svg)

### Installation

```
composer require bradietilley/pest-stories
```

### Overivew

Pest Stories is comprised largely of various `Callback` classes: `Action`, `Assertion` and `Story`.

Each `Callback` class represents a fragment of your application, that is often replicable and common (but not always).

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

An example of where this would come in handy is Policy tests, for example if you have various roles to test for the authorised user, various roles of the end user, whether the authorised and end users belong to the same location. To effectively test this feature, you'd want to test each role (say 8) against each role (8) under each true/false state of "same location", which is 8*8*2 = 128 permutations.

This could be achieved (in a fairly clean way) by Pest Stories. 

### Documentation

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
assertion('200')->as(fn (TestResponse $response) => $response->assertStatus(200)->assertJson([ ... ]));
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
    ])
    /**
     * Now that the stories have been defined, we register the story with Pest. This is similar
     * to running test('...', fn () => ...) against each child story.
     */
    ->register();

/**
 * Test cases:
 * 
 * Create Product endpoint with dataset "with good payload"
 * Create Product endpoint with dataset "with missing slug defaulted to kebab case of title"
 * Create Product endpoint with dataset "fails if missing title"
 */
```

#### Workflow

- `Story` callback: `setUp`
- `Story` Actions:
    - `Action` callback: `before`
    - `Action` primary callback
    - `Action` callback: `after`
- `Story` itself:
    - `Story` callback: `before`
    - `Story` primary callback
    - `Story` callback: `after`
- `Story` Assertions:
    - `Assertion` callback: `before`
    - `Assertion` primary callback
    - `Assertion` callback: `after`
- `Story` callback: `tearDown`



 