# About: Lifecycle

This lifecycle is applicable for a general `Callback` class, and differs slightly for a [Story lifecycle](/docs/stories/about-lifecycle.md). A general `Callback` class is currently `Action`s and `Assertion`s.

When a `Callback` class is processed, it runs the internal Closure callbacks in the following order:

### 1. Before Callbacks:

First up are the `before` callbacks.

You may queue any number of `Closure` callbacks by using the `->before()` method. The closure callbacks are called in the order that they are pushed.

### 2. Primary Callback:

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
