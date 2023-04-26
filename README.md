# Pest Stories

A clean approach for writing large test suites.

![Static Analysis](https://github.com/bradietilley/pest-stories/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/pest-stories/actions/workflows/tests.yml/badge.svg)


## Introduction

User Stories are short, simple descriptions of a feature or functionality of a software application, typically written from the perspective of an end user.

Pest Stories is a PHP package that extends the PestPHP testing framework, allowing developers to write user stories in a clear and reusable way, making it easier to maintain and test their software applications. The idea is your tests should be written in a human readable way that reflects a user story.


## Installation

```
composer require bradietilley/pest-stories --dev
```

To add Stories to your test suites, you must add the following trait via Pest's `uses()` helper:

```php
uses(BradieTilley\Stories\Concerns\Stories::class);
```

*Refer to Pest's documentation on how to use the `uses()` helper.*


## Documentation

Read the [docs](/docs/README.md).

## Example

`tests/Pest.php`:

```php
action('as_admin', function () {
    actingAs(User::factory()->admin()->create());
});

action('as_customer', function () {
    actingAs(User::factory()->customer()->create());
});

action('create_product', function (array $product) {
    return test()->postJson(route('products.store'), array_replace([
        'title' => 'Default',
        'sku' => 'default',
        'price' => 99.99,
    ], $product))
}, 'response');

action('response:ok', function (TestResponse $response) {
    $response->assertOk();
});

action('response:invalid', function (TestResponse $response) {
    $response->assertUnprocessable();
});
```

`tests/Feature/Api/Products/CreateTest.php`:

```php
test('can create a product via the api')
    ->action('as_admin')
    ->assertDatabaseMissing('products', [
        'title' => 'Default',
    ])
    ->action('create_product')
    ->assertDatabaseHas('products', [
        'title' => 'Default',
    ]);

test('cannot create a product via the api as a customer')
    ->action('as_customer')
    ->assertDatabaseMissing('products', [
        'title' => 'Default',
    ])
    ->action('create_product')
    ->assertDatabaseMissing('products', [
        'title' => 'Default',
    ]);

test('can create a product via the api with a custom title')
    ->action('as_admin')
    ->assertDatabaseMissing('products', [ 'title' => 'Custom Product' ])
    ->action('create_product', [
        'product' => [
            'title' => 'Custom Product',
        ],
    ])
    ->assertDatabaseHas('products', [ 'title' => 'Custom Product' ]);

test('can create a product via the api with a custom price')
    ->action('as_admin')
    ->assertDatabaseMissing('products', [ 'price' => 12345.67 ])
    ->action('create_product', [
        'product' => [
            'price' => 12345.67,
        ],
    ])
    ->assertDatabaseHas('products', [ 'price' => 12345.67 ]);
```

Of course this just touches on the possible ways of customising Pest Stories to make it your own.

## Author

- [Bradie Tilley](https://github.com/bradietilley)