# [Stories](/docs/stories/README.md) > Conditionable

Using Laravel's `Conditionable` trait, you can conditionally run methods.

With Pest Stories, you can choose to run your conditionable logic immediately (as it does in Laravel's Conditionable) or you may choose to invoke them lazily when the Story is booted.

## Usage

```php
story('can do something')
    ->action('do_something')
    ->set('abc', '123')
    ->when(
        fn (Story $story) => $story->get('abc') === '123',
        function () {
            $this->action('do_something_else');
        },
    )
    ->lazyWhen(
        fn (Story $story) => $story->get('abc') === '456',
        function () {
            $this->action('do_something_else_again');
        },
    )
    ->setUp(fn (Story $story) => $story->set('abc', '456'))
    ->assertion('assert_something')
    ->test();
```

In this example, the following occurs:

- Add action: `do_something`
- Set `abc` to `123`
- When `abc` is `123` (i.e. `true`)
  - Add action: `do_something_else`
- Add assertion: `assert_something` 
- Story is registered with Pest `test()`
- Behind the scenes:
  - Story is invoked by Pest
- Story process:
  - Run `setUp` callback
    - Set `abc` to `456`
  - Lazy When: `abc` is `456` (i.e. `true`)
    - Add action: `do_something_else_again`
  - Run `Actions`:
    - `do_something`
    - `do_something_else`
    - `do_something_else_again`
  - Run Assertions:
    - `assert_something`

TL;DR: The `when` is immediately invoked while the `lazyWhen` is invoked after `setUp` and immediately before any `Action`.