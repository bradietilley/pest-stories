# [Stories](/docs/stories/README.md) > Pest Expectations

You're 100% familiar with Pest's expectation helper methods like `->toBe()`, `->toHaveCount()`, `->toBeInstanceOf()`, etc.

In Pest Stories, these exact same helper methods are available on any given `Story` object (must follow an expectation `->expect()` call). When you call `->expect()` or `->toBe()` on a story, it remembers the order you've invoked them, then applies them immediately before the `Story`'s assertions are run (after actions are run).

The only way this differs from standard Pest is the `expect` function which is a method `Story` instead of a global function AND the (actual) `$value` you provide to the `expect` method is not a raw value: you must instead specify the [variable name](/docs/stories/feature-variable-data-repository.md) (string) or value resolver callback (`Closure`).

Everything else you love about `expect()` should continue to work without learning anything else.

## Usage

```php
action('as_admin')->as(fn () => (object) [
    'id' => 0,
    'name' => 'Someone',
    'role' => 'admin',
])->for('user');

action('create_invoice')->as(fn () => (object) [
    'id' => 0,
    'items' => collect([
        (object) [
            'item' => 'Test',
            'qty' => 1,
            'price' => 100,
        ],
        (object) [
            'item' => 'Test',
            'qty' => 5,
            'price' => 10,
        ],
        (object) [
            'item' => 'Test',
            'qty' => 2,
            'price' => 25,
        ],
    ]),
    'total' => 200,
])->for('invoice');

story('an admin can create an invoice')
    ->action('as_admin')
    ->action('create_invoice')
    ->action(function () {
        $this->set('order', (object) []);
    })
    /**
     * First expectation using the `user` variable
     */
    ->expect('user')
    ->toBeObject()
    ->toHaveKeys([
        'id',
        'name',
        'role',
    ])
    /**
     * Second expectation using the `invoice` variable
     */
    ->expect('invoice')
    ->toBeObject()
    ->total->toBe(200)
    ->items->toHaveCount(3)
    ->items->get(0)->qty->toBe(1)
    ->items->get(0)->price->toBe(100)
    ->items->get(1)->qty->toBe(5)
    ->items->get(1)->price->toBe(10)
    ->items->get(2)->qty->toBe(2)
    ->items->get(2)->price->toBe(25)
    /**
     * Third expectation example.
     * 
     * Both `expect` and `and` operate the same way.
     */
    ->and('order')
    ->toBeObject()
    /**
     * Fourth expectation example.
     * 
     * You may also resolve the expectation value ourselves
     */
    ->expect(function (Story $story, object $invoice) {
        return $invoice->user_id === auth()->id();
    })
    ->toBeTrue()
    ->story();

```

## Interface & Limitations

When you call `->expect()` on a `Story` object, it returns an instance of `ExpectationChain`. Having a separate class for expectations allows for a cleaner way to catch all methods and properties and pass them to the chained Pest `Expectation` - it's cleaner because the `Story` object is too "noisy" with its many properties and methods, and because of this it will no doubt have higher rate of collision with your custom expectation methods and properties, such as when you need to run a method on the expectation value (e.g. `get()` on `expect($collection)->get(0)->toBeNull()`), which would collide with `Story::get()`.

In order to continue with the story's registration, you need to run `->test()` on the story, or depending on where you define your expectations, you may want to follow on with some child story definitions. There are two available methods that break you out of the `ExpectationChain` and return you to the `Story` object. See the below notice:

>>> You cannot utilise `->story()` or `->stories()` within an expectation, as these are reserved for Pest Stories to "jump back" from an `ExpectationChain` to the governing `Story`. This likely doesn't affect you unless you have an object with one of these two methods.

Example where this would affect you:

```php
# 
class Comment
{
    public function story(): string
    {
        return 'something here';
    }
    
    public function storyText(): string
    {
        return 'something here';
    }
}

// ...

story()
    ->set('comment', new Comment())
    ->expect('comment')
    ->storyText()              // This works
    ->toBe('something here')
    ->story()                  // This won't work because `story()` is reserved.
    ->toBe('something here');
```

## Inheritance

These expectations are inheritable in Pest Stories so that you can define expectations on the parent story and have all children inherit the same expectations.

As mentioned above, in order to continue with the story's registration, you need to run `->test()` on the `Story` object, so you'll need to run `->story()->test()` to break out of the `ExpectationChain` then register the `test()`.
