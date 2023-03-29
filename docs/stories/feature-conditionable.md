# [Stories](/docs/stories/README.md) > Conditionable

Using Laravel's `Conditionable` trait, you can conditionally run methods.

The primary difference between standard Laravel `Conditionable` usage is when the conditionable `when` and `unless` callbacks are run. Normally these would immediately run, however in Pest Stories their execution is deffered until the Story is booted.

## Subject to change

It's making less sense to have them deffered so they might be refatored to simply use Laravel's `Conditionable` trait directly with no deferred logic.

## Usage

```php
story('can do something')
    ->action(fn () => doSomething())
    ->when(
        fn (Story $story) => $story->get('x') === 'y',
        function () {
            $this->action(doSomethingElse());
        },
    )
    ->unless(
        fn (Story $story) => $story->get('a') === 'a',
        function () {
            $this->action(doSomethingElseAgain());
        },
    )
    ->assertion(fn () => assertSomething());
```