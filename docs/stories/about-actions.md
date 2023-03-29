# [Stories](/docs//stories/README.md) > Adding Actions

A `Story` may have `Action` instances added which can form a baseline scenario in which your test is going to utilise.

An example of this could be the creation of an Invoice (action) for you to test the execution of a job that marks the Invoice as paid (action) before confirming the job succesfully marks the invoice as paid ([assertion](/docs/stories/about-assertions.md)).

We've covered how you may go about defining actions [here](/docs/actions/README.md), and in this documenation we'll cover how to utilise the action in a story.

**Creating actions**

As previously documented, there's a few ways of creating an action, these include:

```php
// via the helper function
use function BradieTilley\Stories\Helpers\action;
action('action_name')->as(fn () => echo 'do action logic')->for('custom_variable');

// or via the class itself
use BradieTilley\Stories\Action;
Action::make('action_name')->as(fn () => echo 'do action logic')->for('custom_variable');

// or as a closure
$action = fn () => 'do action logic';
```

**Adding actions**

It's really simple to add an action (or multiple) to a story.

```php
use function BradieTilley\Stories\Helpers\action;
use function BradieTilley\Stories\Helpers\story;

// Creating the action as above
$action = action('action_name')->as(fn () => echo 'do action logic')->for('custom_variable');

// Adding the action by name
story()->action('action_name');
// Under the hood it'll look for the most recently created Action class with the name 'action_name'

// Adding the action by variable / Action instance
story()->action($action);
// Under the hood it just adds the given Action class to the story

// Adding the action as an inline function
story()->action(fn () => echo 'do action logic', for: 'custom_variable');
// Under the hood it'll convert the closure to an Action instance with a randomised name

// Adding many
story()->action([
    'action_name',
    $action,
    fn () => echo 'do action logic',
]);
```

**Adding actions with arguments**

As previously documented, actions may have arguments that you may provide to it. To pass in arguments,
simply provide the `$arguments` argument in the `action()` method. Arguments are to be passed as key/value pairs.

```php
use App\Models\Product;

action(Product::class)->as(function (array $data) {
    return Product::factory()->create($data);
})->for('product');

action(Product::class . ':api:update')->as(function (Product $product, TestCase $test, array $payload = []) {
    $payload = array_replace($product->toArray(), $payload);

    return $test->post(route('products.update', $product), $payload);
})->as('api:response');

story('can still update a product that is marked as end of line')
    // Add actions
    ->action(Product::class, [
        'data' => [
            'title' => 'ABC product',
            'end_of_line' => true,
        ],
    ])
    ->action(Product::class . ':api:update', [
        'payload' => [
            'title' => 'ABC product (no longer sold)',
        ],
    ])
    // Run assertions
    ->expect('api:response')
    ->assertCreated()
    ->toBeTrue()
    ->expect('product')
    ->refresh()
    ->title
    ->toBe('ABC product (no longer sold)');
```

**Workflow**

See [About: Lifecycle](/docs/about-lifecycle.md) for more information on the workflow of Pest Stories.