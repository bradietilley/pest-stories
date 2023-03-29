# [Stories](/docs//stories/README.md) > Adding Assertions

A `Story` may have `Assertion` instances added which can form a baseline scenario in which your test is going to utilise.

An example of this could be the creation of an Invoice (assertion) for you to test the execution of a job that marks the Invoice as paid (assertion) before confirming the job succesfully marks the invoice as paid ([assertion](/docs/stories/about-assertions.md)).

We've covered how you may go about defining assertions [here](/docs/assertions/README.md), and in this documenation we'll cover how to utilise the assertion in a story.

**Creating assertions**

As previously documented, there's a few ways of creating an assertion, these include:

```php
// via the helper function
use function BradieTilley\Stories\Helpers\assertion;
assertion('assertion_name')->as(fn () => echo 'do assertion logic')->for('custom_variable');

// or via the class itself
use BradieTilley\Stories\Assertion;
Assertion::make('assertion_name')->as(fn () => echo 'do assertion logic')->for('custom_variable');

// or as a closure
$assertion = fn () => 'do assertion logic';
```

**Adding assertions**

It's really simple to add an assertion (or multiple) to a story.

```php
use function BradieTilley\Stories\Helpers\assertion;
use function BradieTilley\Stories\Helpers\story;

// Creating the assertion as above
$assertion = assertion('assertion_name')->as(fn () => echo 'do assertion logic')->for('custom_variable');

// Adding the assertion by name
story()->assertion('assertion_name');
// Under the hood it'll look for the most recently created Assertion class with the name 'assertion_name'

// Adding the assertion by variable / Assertion instance
story()->assertion($assertion);
// Under the hood it just adds the given Assertion class to the story

// Adding the assertion as an inline function
story()->assertion(fn () => echo 'do assertion logic', for: 'custom_variable');
// Under the hood it'll convert the closure to an Assertion instance with a randomised name

// Adding many
story()->assertion([
    'assertion_name',
    $assertion,
    fn () => echo 'do assertion logic',
]);
```

**Adding assertions with arguments**

As previously documented, assertions may have arguments that you may provide to it. To pass in arguments,
simply provide the `$arguments` argument in the `assertion()` method. Arguments are to be passed as key/value pairs.

```php
use App\Models\Product;

// todo
```

**Workflow**

See [About: Lifecycle](/docs/about-lifecycle.md) for more information on the workflow of Pest Stories.