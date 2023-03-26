# About: Lifecycle

This lifecycle is applicable for the `Story` class, and differs slightly for a [Generic lifecycle](/docs/about-lifecycle.md).

When a `Story` class is processed, it runs the internal Closure callbacks/processes in the following order:

### 1. Set Up Callbacks:

First up are the `setUp` callbacks.

These run immediately when the test is processed after the `TestCase` is made available to the `Story`.

You may queue any number of `Closure` callbacks by using the `->setUp()` method. The closure callbacks are called in the order that they are pushed.

### 2. Before Callbacks:

Next up are the `before` callbacks.

You may queue any number of `Closure` callbacks by using the `->before()` method. The closure callbacks are called in the order that they are pushed.

### 3. Conditionables

Next up is the execution of the Laravel Conditionables that were registered.

Just like Laravel's Conditionable, you may when conditional based logic, using the  `->when()` and `->unless()` methods.

### 4. Actions

Next up are [Story Actions](/docs/stories/about-actions.md).

### 5. Primary Callback:

Then comes the primary callback.

You may choose for the `Callback` class to have a primary `Closure` callback which can be specified by using the `->as()` method.























### 3. After Callbacks:

Last up are the `after` callbacks.

You may queue any number of `Closure` callbacks by using the `->after()` method. The closure callbacks are called in the order that they are pushed.


---

### Example:

```php
/** @var Action|Assertion|Story $callback */
$callback = ...;

$callback->as(fn () => dump('Primary'))
    ->before(fn () => dump('Before1'))
    ->before(fn () => dump('Before2'))
    ->after(fn () => dump('After1'))
    ->after(fn () => dump('After2'));

$story->process();

/**
 * Output:
 *      Before1
 *      Before2
 *      Primary
 *      After1
 *      After2
 */
```
